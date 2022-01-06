<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\FileManager;
use EasyTool\Framework\App\Http\Server\Request as HttpRequest;
use EasyTool\Framework\App\Http\Server\Request\Handler as HttpRequestHandler;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use Symfony\Component\Console\Application as ConsoleApplication;

class App
{
    private CacheManager $cacheManager;
    private ConfigManager $configManager;
    private EventManager $eventManager;
    private FileManager $fileManager;
    private ModuleManager $moduleManager;
    private ObjectManager $objectManager;
    private ClassLoader $classLoader;

    private string $directoryRoot;

    public function __construct(
        CacheManager $cacheManager,
        ConfigManager $configManager,
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
        $this->moduleManager->initialize($this->classLoader);
    }

    public function getVersion()
    {
        $composerConfig = json_decode($this->fileManager->getFileContents('composer.lock'), true);
        foreach ($composerConfig['packages'] as $package) {
            if ($package['name'] == 'easy-tool/framework') {
                return $package['extra']['branch-alias'][$package['version']] ?? $package['version'];
            }
        }
    }

    public function handleCommand()
    {
        /* @var $consoleApplication ConsoleApplication */
        $consoleApplication = $this->objectManager->create(
            ConsoleApplication::class,
            ['name' => 'EasyTool', 'version' => $this->getVersion()]
        );
        $consoleApplication->run();
    }

    public function handleHttp()
    {
        /** @var HttpRequest $httpRequest */
        $httpRequest = $this->objectManager->get(HttpRequest::class);
        $httpRequestHandler = $this->objectManager->get(HttpRequestHandler::class);
        $httpRequestHandler->handle($httpRequest);
    }
}
