<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;

class App
{
    private App\Config $config;
    private App\Event\Manager $eventManager;
    private App\ObjectManager $objectManager;
    private ClassLoader $composerLoader;

    public function __construct(
        App\Config $config,
        App\Event\Manager $eventManager,
        App\ObjectManager $objectManager,
        ClassLoader $composerLoader
    ) {
        $this->composerLoader = $composerLoader;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;
    }

    public function handleHttp()
    {
    }

    public function handleCommand()
    {
    }
}
