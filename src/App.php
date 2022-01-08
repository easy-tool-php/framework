<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\FileManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

class App
{
    private CacheManager $cacheManager;
    private ConfigManager $configManager;
    private DatabaseManager $databaseManager;
    private EventManager $eventManager;
    private FileManager $fileManager;
    private ModuleManager $moduleManager;
    private ObjectManager $objectManager;
    private ClassLoader $classLoader;

    private string $directoryRoot;

    public function __construct(
        CacheManager $cacheManager,
        ConfigManager $configManager,
        DatabaseManager $databaseManager,
        EventManager $eventManager,
        FileManager $fileManager,
        ModuleManager $moduleManager,
        ObjectManager $objectManager,
        ClassLoader $classLoader,
        string $directoryRoot
    ) {
        $this->cacheManager = $cacheManager;
        $this->classLoader = $classLoader;
        $this->configManager = $configManager;
        $this->databaseManager = $databaseManager;
        $this->directoryRoot = $directoryRoot;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;

        $this->initialize();
    }

    private function initialize()
    {
        /**
         * Initializing of file manager MUST be executed at the first,
         *     otherwise the system will not be able to find the correct position of config files.
         */
        $this->fileManager->initialize($this->directoryRoot);
        $this->objectManager->initialize();
        $this->eventManager->initialize();
        $this->cacheManager->initialize();
        $this->databaseManager->initialize();
        $this->moduleManager->initialize($this->classLoader);
    }

    /**
     * Return current version of the framework
     */
    public function getVersion()
    {
        $composerConfig = json_decode($this->fileManager->getFileContents('composer.lock'), true);
        foreach ($composerConfig['packages'] as $package) {
            if ($package['name'] == 'easy-tool/framework') {
                return $package['extra']['branch-alias'][$package['version']] ?? $package['version'];
            }
        }
    }

    public function handleCommand(): void
    {
        /** @var ConsoleApplication $consoleApplication */
        $consoleApplication = $this->objectManager->get(
            ConsoleApplication::class,
            ['name' => 'EasyTool', 'version' => $this->getVersion()]
        );
        $consoleApplication->run();
    }

    public function handleHttp(): void
    {
        /** @var ServerRequestInterface $httpRequest */
        /** @var RequestHandlerInterface $httpRequestHandler */
        $httpRequest = $this->objectManager->get(ServerRequestInterface::class);
        $httpRequestHandler = $this->objectManager->get(RequestHandlerInterface::class);
        $httpRequestHandler->handle($httpRequest);
    }
}
