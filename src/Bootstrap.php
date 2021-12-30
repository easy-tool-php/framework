<?php

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;

class Bootstrap
{
    private static ?Bootstrap $instance = null;

    public static function getInstance(): Bootstrap
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createApplication(ClassLoader $composerLoader): App
    {
        return App\ObjectManager::getInstance()
            ->create(App::class, ['composerLoader' => $composerLoader]);
    }
}
