<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Exception\Handler as ExceptionHandler;
use EasyTool\Framework\App\FileManager;
use EasyTool\Framework\App\Http\Server\Response\Handler as HttpResponseHandler;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;

class App
{
    public const FRAMEWORK_NAME = 'EasyTool';
    public const PACKAGE_NAME = 'easy-tool/framework';

    private Area $area;
    private DatabaseManager $databaseManager;
    private EventManager $eventManager;
    private FileManager $fileManager;
    private ModuleManager $moduleManager;
    private ObjectManager $objectManager;
    private ClassLoader $classLoader;

    private string $directoryRoot;

    public function __construct(
        Area $area,
        DatabaseManager $databaseManager,
        EventManager $eventManager,
        FileManager $fileManager,
        ModuleManager $moduleManager,
        ObjectManager $objectManager,
        ClassLoader $classLoader,
        string $directoryRoot
    ) {
        $this->area = $area;
        $this->classLoader = $classLoader;
        $this->databaseManager = $databaseManager;
        $this->directoryRoot = $directoryRoot;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;

        $this->initialize();
    }

    private function initialize(): void
    {
        /**
         * Use UTC time as system time, for calculation and storage
         */
        ini_set('date.timezone', 'UTC');

        /**
         * Initializing of file manager MUST be executed at the first,
         *     otherwise the system will not be able to find the correct position of config files.
         */
        $this->fileManager->initialize($this->directoryRoot);
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
     * Return current version of the framework
     */
    public function getVersion(): ?string
    {
        $composerConfig = json_decode($this->fileManager->getFileContents('composer.lock'), true);
        foreach ($composerConfig['packages'] as $package) {
            if ($package['name'] == self::PACKAGE_NAME) {
                return $package['extra']['branch-alias'][$package['version']] ?? $package['version'];
            }
        }
        return null;
    }

    /**
     * Collect classes which extends `\Symfony\Component\Console\Command\Command` from specified directory,
     *     and add them into the console application
     */
    private function addCommands($consoleApplication, $dir, $namespace)
    {
        $files = $this->fileManager->getFiles($dir, true, true);
        foreach ($files as $file) {
            if (($pos = strrpos($file, '.')) && strtolower(substr($file, $pos)) == '.php') {
                $class = '\\' . $namespace
                    . str_replace('/', '\\', substr($file, 0, $pos));
                $reflectionClass = new ReflectionClass($class);
                if ($reflectionClass->isSubclassOf(Command::class)) {
                    $consoleApplication->add($this->objectManager->create($class));
                }
            }
        }
    }

    /**
     * Handle console command
     */
    public function handleCommand(): void
    {
        $this->area->setCode(Area::CLI);

        /** @var ConsoleApplication $consoleApplication */
        $consoleApplication = $this->objectManager->get(
            ConsoleApplication::class,
            ['name' => self::FRAMEWORK_NAME, 'version' => $this->getVersion()]
        );
        $this->addCommands(
            $consoleApplication,
            __DIR__ . '/App/Command',
            self::class . '\\Command\\'
        );
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            $this->addCommands(
                $consoleApplication,
                $module[ModuleManager::MODULE_DIR] . '/Command',
                $module[ModuleManager::MODULE_NAMESPACE] . 'Command\\'
            );
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
