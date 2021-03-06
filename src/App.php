<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Exception\Handler as ExceptionHandler;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\App\Http\Server\Response\Handler as HttpResponseHandler;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
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

    private ClassLoader $classLoader;
    private DiContainer $diContainer;
    private ModuleManager $moduleManager;
    private string $dirRoot;

    public function __construct(
        ClassLoader $classLoader,
        DiContainer $diContainer,
        Directory $directory,
        string $dirRoot
    ) {
        $this->classLoader = $classLoader;
        $this->diContainer = $diContainer;
        $this->dirRoot = $dirRoot;

        $directory->setRoot($dirRoot);
    }

    /**
     * Initialization is done at the beginning of handle methods instead of class construct,
     *     so that to prevent circular dependency.
     */
    private function initialize(): void
    {
        /**
         * Use UTC time as system time, for calculation and storage
         */
        ini_set('date.timezone', 'UTC');

        /** @var CacheManager $cacheManager */
        /** @var EventManager $eventManager */
        $cacheManager = $this->diContainer->get(CacheManager::class);
        $eventManager = $this->diContainer->get(EventManager::class);
        $moduleManager = $this->diContainer->get(ModuleManager::class);

        $cacheManager->initialize();
        $eventManager->initialize();
        $moduleManager->initialize($this->classLoader);
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
        $composerConfig = json_decode(file_get_contents($this->dirRoot . '/composer.lock'), true);
        foreach ($composerConfig['packages'] as $package) {
            if ($package['name'] == self::PACKAGE_NAME) {
                return $package['extra']['branch-alias'][$package['version']] ?? $package['version'];
            }
        }
        return null;
    }

    /**
     * Handle console command
     *
     * Collect all classes which extend the `\Symfony\Component\Console\Command\Command`
     *     from 2 places:
     * - `App/Command` directory of the framework
     * - `Command` directory of all modules
     */
    public function handleCommand(): void
    {
        $this->initialize();
        $this->diContainer->get(Area::class)->setCode(Area::CLI);

        /** @var ConsoleApplication $consoleApplication */
        /** @var DirectoryScanner $scanner */
        $consoleApplication = $this->diContainer->create(
            ConsoleApplication::class,
            ['name' => self::FRAMEWORK_NAME, 'version' => $this->getVersion()]
        );
        $scanner = $this->diContainer->get(DirectoryScanner::class);
        $scanner->addDirectory(__DIR__ . '/App/Command');
        foreach ($this->diContainer->get(ModuleManager::class)->getEnabledModules() as $module) {
            if (is_dir(($directory = $module[ModuleManager::MODULE_DIR] . '/Command'))) {
                $scanner->addDirectory($directory);
            }
        }
        foreach ($scanner->getClassNames() as $className) {
            $reflectionClass = new ReflectionClass($className);
            if (
                $reflectionClass->isSubclassOf(Command::class)
                && $reflectionClass->isInstantiable()
            ) {
                $consoleApplication->add($this->diContainer->create($className));
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
        set_exception_handler([$this->diContainer->get(ExceptionHandler::class), 'handle']);

        /** @var ServerRequestInterface $httpRequest */
        /** @var RequestHandlerInterface $httpRequestHandler */
        /** @var HttpResponseHandler $httpResponseHandler */
        $httpRequest = $this->diContainer->get(ServerRequestInterface::class);
        $httpRequestHandler = $this->diContainer->get(RequestHandlerInterface::class);
        $httpResponseHandler = $this->diContainer->get(HttpResponseHandler::class);
        $httpResponseHandler->handle($httpRequestHandler->handle($httpRequest));
    }
}
