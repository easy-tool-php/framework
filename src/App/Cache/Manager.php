<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Cache\Adapter\AdapterInterface;
use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\ObjectManager;

class Manager
{
    public const CONFIG_NAME = 'cache';
    public const CONFIG_ENV_PATH = 'cache';

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
     * Create an adapter instance with given config
     */
    private function getAdapterInstance($config): AdapterInterface
    {
        switch ($config['adapter']) {
            case Adapter\Files::CODE:
                return $this->objectManager->create(Adapter\Files::class);

            default:
                return $this->objectManager->create($config['adapter'])->setConfig($config);
        }
    }

    /**
     * Get cache by given name
     */
    public function getCache(string $name): Cache
    {
        if (!isset($this->caches[$name])) {
            $status = $this->config->get(null, self::CONFIG_NAME);
            $this->caches[$name] = $this->objectManager->create(
                Cache::class,
                [
                    'adapter' => $this->getAdapterInstance($this->config->getEnv(self::CONFIG_ENV_PATH)),
                    'name' => $name,
                    'isEnabled' => $status[$name] ?? true
                ]
            );
        }
        return $this->caches[$name];
    }
}
