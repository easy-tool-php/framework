<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;

class App
{
    private App\Config $config;
    private App\Event\Manager $eventManager;
    private App\Filesystem\DirectoryManager $directoryManager;
    private App\Filesystem\FileManager $fileManager;
    private App\ObjectManager $objectManager;
    private ClassLoader $composerLoader;
    private string $directoryRoot;

    public function __construct(
        App\Config $config,
        App\Event\Manager $eventManager,
        App\Filesystem\DirectoryManager $directoryManager,
        App\Filesystem\FileManager $fileManager,
        App\ObjectManager $objectManager,
        ClassLoader $composerLoader,
        string $directoryRoot
    ) {
        $this->composerLoader = $composerLoader;
        $this->config = $config;
        $this->directoryRoot = $directoryRoot;
        $this->directoryManager = $directoryManager;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
        $this->objectManager = $objectManager;

        $this->initialize();
    }

    private function initialize()
    {
        $this->fileManager->initialize($this->directoryRoot);
        $this->eventManager->initialize();
    }

    public function getVersion()
    {
    }

    public function handleCommand()
    {
        /* @var $consoleApplication ConsoleApplication */
        $consoleApplication = $this->objectManager->create(
            ConsoleApplication::class,
            [
                'name' => 'EasyTool',
                'version' => $this->getVersion()
            ]
        );
        $consoleApplication->run();
    }

    public function handleHttp()
    {
    }
}
