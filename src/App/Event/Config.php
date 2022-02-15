<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'events.php';
    protected array $format = [
        '*.*.listener' => ['required', 'string'],
        '*.*.order'    => ['int']
    ];
}
