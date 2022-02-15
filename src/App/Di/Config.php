<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Di;

use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'di.php';
    protected array $format = [
        'preferences'       => ['array'],
        'types'             => ['array'],
        'types.parameters'  => ['array'],
        'types.preferences' => ['array']
    ];
}
