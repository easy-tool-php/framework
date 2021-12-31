<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Config\Configurable;

class Manager
{
    use Configurable;

    public const CONFIG_NAME = 'cache';

    /**
     * Collect config data from `app/config/cache.php` and add initialize caches
     */
    public function initialize(): void
    {
    }
}
