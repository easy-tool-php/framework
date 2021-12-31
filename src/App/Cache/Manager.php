<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Config\AppConfig;
use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\ObjectManager;

class Manager
{
    public const CONFIG_NAME = 'cache';

    private AppConfig $config;
    private ConfigManager $configManager;
    private ObjectManager $objectManager;
    private array $caches = [];

    public function __construct(
        ConfigManager $configManager,
        ObjectManager $objectManager
    ) {
        $this->configManager = $configManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Get adapter info from config file
     */
    public function initialize(): void
    {
        $this->config = $this->configManager->getConfig(self::CONFIG_NAME);
    }

    /**
     * Get cache by given name
     */
    public function getCache(string $name)
    {
        if (!isset($this->caches[$name])) {
            $this->caches[$name] = $this->objectManager->create(
                Cache::class,
                ['adapter' => $this->objectManager->create($this->config->get('adapter'))]
            );
        }
        return $this->caches[$name];
    }
}
