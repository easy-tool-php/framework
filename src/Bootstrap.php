<?php

namespace EasyTool\Framework;

class Bootstrap
{
    private static Bootstrap $instance;

    private App\ObjectManager $objectManager;

    public static function getInstance(): Bootstrap
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->objectManager = App\ObjectManager::getInstance();
    }

    public function createApplication(): App
    {
        return $this->objectManager->get(App::class);
    }
}
