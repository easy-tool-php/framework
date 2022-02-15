<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Cache\Adapter\Filesystem;

use Laminas\Cache\Storage\Adapter\AdapterOptions;

class Options extends AdapterOptions
{
    private ?string $cacheDir = null;

    /**
     * Set absolute path of cache directory
     */
    public function setCacheDir(string $cacheDir): self
    {
        if ($this->cacheDir === $cacheDir) {
            return $this;
        }
        $this->triggerOptionEvent('cache_dir', $cacheDir);
        $this->cacheDir = $cacheDir;
        return $this;
    }

    /**
     * Returns absolute path of cache directory
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }
}
