<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Config\Configurable;
use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\ObjectManager;

class Manager
{
    public const CONFIG_NAME = 'cache';

    private ConfigManager $configManager;
    private ObjectManager $objectManager;
    private array $caches = [];
    private string $adapter;

    public function __construct(
        ConfigManager $configManager,
        ObjectManager $objectManager
    ) {
        $this->configManager = $configManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Collect config data from `app/config/cache.php` and add initialize caches
     */
    public function initialize(): void
    {
        $config = $this->configManager->getConfig(self::CONFIG_NAME);
        $this->adapter = $config->get('adapter');
    }

    /**
     * Get cache by given name
     */
    public function getCache(string $name)
    {
        if (!isset($this->caches[$name])) {
            $this->caches[$name] = $this->objectManager->create(
                Cache::class,
                ['adapter' => $this->objectManager->create($this->adapter)]
            );
        }
        return $this->caches[$name];
    }
}
