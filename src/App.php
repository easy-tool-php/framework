<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Exception\FileException;
use EasyTool\Framework\App\Exception\Handler as ExceptionHandler;
use EasyTool\Framework\App\Http\Server\Response\Handler as HttpResponseHandler;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use Laminas\Code\Scanner\DirectoryScanner;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;

class App
{
    public const FRAMEWORK_NAME = 'EasyTool';
    public const PACKAGE_NAME = 'easy-tool/framework';

    public const DIR_APP = 'app';
    public const DIR_CACHE = 'cache';
    public const DIR_CONFIG = 'config';
    public const DIR_LOG = 'log';
    public const DIR_MODULES = 'app/modules';
    public const DIR_PUB = 'pub';
    public const DIR_ROOT = 'root';
    public const DIR_TMP = 'tmp';
    public const DIR_VAR = 'var';

    private Area $area;
    private DatabaseManager $databaseManager;
    private EventManager $eventManager;
    private ModuleManager $moduleManager;
    private ObjectManager $objectManager;
    private ClassLoader $classLoader;

    private string $directoryRoot;

    public function __construct(
        ClassLoader $classLoader,
        ObjectManager $objectManager,
        string $directoryRoot
    ) {
        $this->classLoader = $classLoader;
        $this->directoryRoot = $directoryRoot;
        $this->objectManager = $objectManager;
    }

    /**
     * Initializing need to be done at the beginning of handle methods instead of class construct,
     *     because the App singleton may also injected by some classes which may cause dead loop.
     */
    private function initialize(): void
    {
        /**
         * Use UTC time as system time, for calculation and storage
         */
        ini_set('date.timezone', 'UTC');

        /**
         * Getting below singletons here instead of through dependency injection
         *     in order to avoid dead loop.
         */
        $this->area = $this->objectManager->get(Area::class);
        $this->databaseManager = $this->objectManager->get(DatabaseManager::class);
        $this->eventManager = $this->objectManager->get(EventManager::class);
        $this->moduleManager = $this->objectManager->get(ModuleManager::class);

        $this->objectManager->initialize();
        $this->eventManager->initialize();
        $this->databaseManager->initialize();
        $this->moduleManager->initialize($this->classLoader);
    }

    /**
     * Returns the Composer class loader instance
     */
    public function getClassLoader(): ClassLoader
    {
        return $this->classLoader;
    }

    /**
     * Get absolute path of directory with specified type,
     *     the types are defined as static variables of this class.
     *
     * @throws FileException
     */
    public function getDirectoryPath(string $type): string
    {
        switch ($type) {
            case self::DIR_ROOT:
                return $this->directoryRoot . '/';

            case self::DIR_APP:
                return $this->directoryRoot . '/app';

            case self::DIR_CONFIG:
                return $this->directoryRoot . '/app/config';

            case self::DIR_MODULES:
                return $this->directoryRoot . '/app/modules';

            case self::DIR_PUB:
                return $this->directoryRoot . '/pub';

            case self::DIR_VAR:
                return $this->directoryRoot . '/var';

            case self::DIR_CACHE:
                return $this->directoryRoot . '/var/cache';

            case self::DIR_LOG:
                return $this->directoryRoot . '/var/log';

            case self::DIR_TMP:
                return $this->directoryRoot . '/var/tmp';

            default:
                throw new FileException('Directory type is not supported.');
        }
    }

    /**
     * Return current version of the framework
     */
    public function getVersion(): ?string
    {
        $composerConfig = json_decode(file_get_contents($this->directoryRoot . '/composer.lock'), true);
        foreach ($composerConfig['packages'] as $package) {
            if ($package['name'] == self::PACKAGE_NAME) {
                return $package['extra']['branch-alias'][$package['version']] ?? $package['version'];
            }
        }
        return null;
    }

    /**
     * Handle console command
     */
    public function handleCommand(): void
    {
        $this->initialize();
        $this->area->setCode(Area::CLI);

        /** @var ConsoleApplication $consoleApplication */
        /** @var DirectoryScanner $scanner */
        $consoleApplication = $this->objectManager->get(
            ConsoleApplication::class,
            ['name' => self::FRAMEWORK_NAME, 'version' => $this->getVersion()]
        );
        $scanner = $this->objectManager->get(DirectoryScanner::class);
        $scanner->addDirectory(__DIR__ . '/App/Command');
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            if (is_dir(($directory = $module[ModuleManager::MODULE_DIR] . '/Command'))) {
                $scanner->addDirectory($directory);
            }
        }
        foreach ($scanner->getClassNames() as $className) {
            $reflectionClass = new ReflectionClass($className);
            if ($reflectionClass->isSubclassOf(Command::class)) {
                $consoleApplication->add($this->objectManager->create($className));
            }
        }
        $consoleApplication->run();
    }

    /**
     * Handle HTTP request
     *
     * To get singletons from the inner method instead of dependency injection,
     *     because we didn't have all necessary things at the beginning.
     */
    public function handleHttp(): void
    {
        $this->initialize();
        set_exception_handler([$this->objectManager->get(ExceptionHandler::class), 'handle']);

        /** @var ServerRequestInterface $httpRequest */
        /** @var RequestHandlerInterface $httpRequestHandler */
        /** @var HttpResponseHandler $httpResponseHandler */
        $httpRequest = $this->objectManager->get(ServerRequestInterface::class);
        $httpRequestHandler = $this->objectManager->get(RequestHandlerInterface::class);
        $httpResponseHandler = $this->objectManager->get(HttpResponseHandler::class);
        $httpResponseHandler->handle($httpRequestHandler->handle($httpRequest));
    }
}
