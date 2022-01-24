<?php

namespace EasyTool\Framework\App\Cache\Adapter;

use Laminas\Cache\Storage\Adapter\AbstractAdapter;

interface FactoryInterface
{
    /**
     * Create cache storage adapter
     */
    public function create(array $options): AbstractAdapter;
}
