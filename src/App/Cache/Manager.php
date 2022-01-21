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
    private array $status = [];

    public function __construct(
        Config $config,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->status = $this->config->get(null, self::CONFIG_NAME);
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
     * Get all caches
     */
    public function getAllCaches()
    {
        $status = $this->config->get(null, self::CONFIG_NAME);
        foreach (array_keys($this->caches) as $cacheName) {
            if (!isset($status[$cacheName])) {
                $this->getCache($cacheName);
            }
        }
        return $this->caches;
    }

    /**
     * Get cache by given name
     */
    public function getCache(string $name): Cache
    {
        if (!isset($this->caches[$name])) {
            if (!isset($this->status[$name])) {
                $this->status[$name] = true;
            }
            $this->caches[$name] = $this->objectManager->create(
                Cache::class,
                [
                    'adapter' => $this->getAdapterInstance($this->config->getEnv(self::CONFIG_ENV_PATH)),
                    'name' => $name,
                    'isEnabled' => $this->status[$name]
                ]
            );
        }
        return $this->caches[$name];
    }

    /**
     * Enable specified cache
     */
    public function enable(string $name): self
    {
        if (!isset($this->status[$name]) && !isset($this->caches)) {
            throw new InvalidArgumentException('Invalid cache name.');
        }
        $this->status[$name] = true;
        return $this;
    }

    /**
     * Disable specified cache
     */
    public function disable(string $name): self
    {
        if (!isset($this->status[$name]) && !isset($this->caches)) {
            throw new InvalidArgumentException('Invalid cache name.');
        }
        $this->status[$name] = false;
        return $this;
    }
}
