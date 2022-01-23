<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Cache\Adapter\AdapterInterface;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Env\Config as EnvConfig;

class Manager
{
    public const CONFIG_PATH = 'cache';

    private DiContainer $diContainer;
    private EnvConfig $envConfig;
    private array $caches = [];
    private array $status = [];

    public function __construct(
        Config $config,
        EnvConfig $envConfig,
        DiContainer $diContainer
    ) {
        $this->diContainer = $diContainer;
        $this->envConfig = $envConfig;
        $this->status = $config->getData();
    }

    /**
     * Create an adapter instance with given config
     */
    private function getAdapterInstance($config): AdapterInterface
    {
        switch ($config['adapter']) {
            case Adapter\Files::CODE:
                return $this->diContainer->create(Adapter\Files::class);

            default:
                return $this->diContainer->create($config['adapter'])->setConfig($config);
        }
    }

    /**
     * Get all caches
     */
    public function getAllCaches()
    {
        foreach (array_keys($this->caches) as $cacheName) {
            if (!isset($this->status[$cacheName])) {
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
            $this->caches[$name] = $this->diContainer->create(
                Cache::class,
                [
                    'adapter' => $this->getAdapterInstance($this->envConfig->get(self::CONFIG_PATH)),
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
