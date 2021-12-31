<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Config\Config;
use EasyTool\Framework\App\Config\Configurable;
use EasyTool\Framework\App\Config\Manager as ConfigManager;

class Manager
{
    public const CONFIG_NAME = 'cache';

    private Config $config;

    public function __construct(ConfigManager $configManager)
    {
        $this->config = $configManager->getConfig(self::CONFIG_NAME);
    }

    /**
     * Collect config data from `app/config/cache.php` and add initialize caches
     */
    public function initialize(): void
    {
    }
}
