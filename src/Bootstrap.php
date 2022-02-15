<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Di\Container as DiContainer;
use Laminas\Di\Config;

/**
 * This item is used to initialize the dependency injection
 *     which is served for managing the objects for the whole framework.
 */
class Bootstrap
{
    private static ?self $instance = null;

    /**
     * An application must work with a class loader and have a specified directory root.
     * All application level configuration are located at `app/config` folder
     *     including the dependency injection.
     */
    public function createApplication(ClassLoader $classLoader, $dirRoot): App
    {
        /** @var Config $config */
        $container = DiContainer::getInstance()->appendConfig(require $dirRoot . '/app/config/di.php');
        $app = $container->create(App::class, [
            'classLoader' => $classLoader,
            'dirRoot'     => $dirRoot
        ]);
        $container->setInstance(App::class, $app);
        return $app;
    }

    /**
     * Get bootstrap singleton
     */
    public static function getInstance(): Bootstrap
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
