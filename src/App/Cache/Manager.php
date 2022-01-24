<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Cache\Adapter\FactoryInterface;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Env\Config as EnvConfig;
use Laminas\Cache\Psr\CacheItemPool\CacheItemPoolDecorator;
use Laminas\Cache\Storage\Plugin\Serializer;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Manager
{
    public const ENV_PATH = 'cache';

    private CacheItemPoolInterface $cachePool;
    private DiContainer $diContainer;
    private EnvConfig $envConfig;
    private array $storageFactories;

    public function __construct(
        DiContainer $diContainer,
        EnvConfig $envConfig,
        array $storageFactories = []
    ) {
        $this->diContainer = $diContainer;
        $this->envConfig = $envConfig;
        $this->storageFactories = $storageFactories;
    }

    /**
     * Implements cache pool based on environment config
     */
    public function initialize(): void
    {
        $configData = $this->envConfig->get(self::ENV_PATH);
        if (!isset($this->storageFactories[$configData['adapter']])) {
            throw new \DomainException('Specified cache storage adapter does not exist.');
        }
        /** @var FactoryInterface $storageFactory */
        $storageFactory = $this->storageFactories[$configData['adapter']];
        $storage = $storageFactory->create($configData['options'] ?? []);
        $this->cachePool = $this->diContainer->create(
            CacheItemPoolDecorator::class,
            ['storage' => $storage->addPlugin(new Serializer())]
        );
        $this->diContainer->setInstance(CacheItemPoolInterface::class, $this->cachePool);
    }

    /**
     * Returns cache item with specified name
     */
    public function getCache(string $name): CacheItemInterface
    {
        return $this->cachePool->getItem($name);
    }

    /**
     * Save cache data with specified name
     */
    public function saveCache(string $name): self
    {
        $this->cachePool->save($this->cachePool->getItem($name));
        return $this;
    }
}
