<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Cache\Adapter;

use Laminas\Cache\Storage\Adapter\AbstractAdapter;

interface FactoryInterface
{
    /**
     * Create cache storage adapter
     */
    public function create(array $options): AbstractAdapter;
}
