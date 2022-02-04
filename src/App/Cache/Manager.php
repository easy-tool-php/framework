<?php

namespace EasyTool\Framework\App\Cache;

use DomainException;
use EasyTool\Framework\App\Cache\Adapter\FactoryInterface;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Env\Config as EnvConfig;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\Code\Generator\ArrayGenerator;
use Laminas\Cache\Psr\CacheItemPool\CacheItemPoolDecorator;
use Laminas\Cache\Storage\Plugin\Serializer;
use Laminas\Code\Generator\FileGenerator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class Manager
{
    public const ENV_PATH = 'cache';

    private ?CacheItemPoolInterface $cachePool = null;
    private DiContainer $diContainer;
    private Directory $directory;
    private EnvConfig $envConfig;
    private array $cacheItems;
    private array $storageFactories;

    public function __construct(
        DiContainer $diContainer,
        Directory $directory,
        EnvConfig $envConfig,
        array $cacheItems = [],
        array $storageFactories = []
    ) {
        $this->cacheItems = $cacheItems;
        $this->diContainer = $diContainer;
        $this->directory = $directory;
        $this->envConfig = $envConfig;
        $this->storageFactories = $storageFactories;
    }

    /**
     * Absolute filepath of cache status
     */
    private function getStatusFile(): string
    {
        return $this->directory->getDirectoryPath(Directory::CONFIG) . '/cache.php';
    }

    /**
     * Save status
     */
    private function saveStatus(): self
    {
        FileGenerator::fromArray(
            [
                'filename' => $this->getStatusFile(),
                'body'     => sprintf("return %s;\n", ArrayGenerator::fromArray($this->cacheItems)->generate())
            ]
        )->write();
        return $this;
    }

    /**
     * Implements cache pool based on environment config
     */
    public function initialize(): void
    {
        $configData = $this->envConfig->get(self::ENV_PATH);
        if (!isset($this->storageFactories[$configData['adapter']])) {
            throw new DomainException('Specified cache storage adapter does not exist.');
        }
        /** @var FactoryInterface $storageFactory */
        $storageFactory = $this->diContainer->get($this->storageFactories[$configData['adapter']]);
        $storage = $storageFactory->create($configData['options'] ?? []);
        $this->cachePool = $this->diContainer->create(
            CacheItemPoolDecorator::class,
            ['storage' => $storage->addPlugin(new Serializer())]
        );
        $this->cacheItems = require $this->getStatusFile();
    }

    /**
     * Check whether specified cache is enabled
     */
    public function isEnabled(string $name): bool
    {
        if (!isset($this->cacheItems[$name])) {
            throw new DomainException('Specified cache is not registered.');
        }
        return $this->cacheItems[$name];
    }

    /**
     * Set status of specified cache
     */
    public function setStatus(string $name, bool $status): self
    {
        if (!isset($this->cacheItems[$name])) {
            throw new DomainException('Specified cache is not registered.');
        }
        $this->cacheItems[$name] = $status;
        return $this->saveStatus();
    }

    /**
     * Register a cache with specified name to add enable/disable handle
     */
    public function register(string $name, bool $enabled = true): self
    {
        if (isset($this->cacheItems[$name])) {
            return $this;
        }
        $this->cacheItems[$name] = $enabled;
        return $this->saveStatus();
    }

    /**
     * Get all registered cache
     */
    public function getRegisteredCaches(): array
    {
        return $this->cacheItems;
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
    public function saveCache(CacheItemInterface $cacheItem): self
    {
        $this->cachePool->save($cacheItem);
        return $this;
    }
}
