<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Cache\Manager as CacheManager;

abstract class AbstractCacheableConfig extends AbstractConfig
{
    public const CACHE_NAME = 'cache';

    private CacheManager $cacheManager;
    private string $name;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        if (($cache = $this->cacheManager->getCache(self::CACHE_NAME))) {
            $cachedData = $cache->get();
            if (isset($cachedData[$this->name])) {
                $data = $cachedData[$this->name];
            }
        }
        parent::__construct($data ?? []);
    }
}
