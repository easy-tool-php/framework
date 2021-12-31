<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application as ConsoleApplication;

class App
{
    private App\Cache\Manager $cacheManager;
    private App\Config $config;
    private App\Event\Manager $eventManager;
    private App\FileManager $fileManager;
    private App\Module\Manager $moduleManager;
    private App\ObjectManager $objectManager;
    private ClassLoader $classLoader;
    private string $directoryRoot;

    public function __construct(
        App\Cache\Manager $cacheManager,
        App\Config $config,
        App\Event\Manager $eventManager,
        App\FileManager $fileManager,
        App\Module\Manager $moduleManager,
        App\ObjectManager $objectManager,
        ClassLoader $classLoader,
        string $directoryRoot
    ) {
        $this->cacheManager = $cacheManager;
        $this->classLoader = $classLoader;
        $this->config = $config;
        $this->directoryRoot = $directoryRoot;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;

        $this->initialize();
    }

    private function initialize()
    {
        $this->fileManager->initialize($this->directoryRoot);
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
    }
}
