<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\ObjectManager;

class Manager
{
    public const CONFIG_NAME = 'cache';
    public const ATTR_ADAPTER = 'adapter';
    public const ATTR_STATUS = 'status';

    private Config $config;
    private ObjectManager $objectManager;
    private array $caches = [];

    public function __construct(
        Config $config,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Get cache by given name
     */
    public function getCache(string $name): Cache
    {
        if (!isset($this->caches[$name])) {
            $status = $this->config->get(self::ATTR_STATUS, self::CONFIG_NAME);
            $this->caches[$name] = $this->objectManager->create(
                Cache::class,
                [
                    'adapter' => $this->objectManager->get($this->config->get(self::ATTR_ADAPTER, self::CONFIG_NAME)),
                    'name' => $name,
                    'isEnabled' => $status[$name] ?? true
                ]
            );
        }
        return $this->caches[$name];
    }
}
