<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Module;

use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'module.php';
    protected array $format = [
        'name'    => ['required', 'string'],
        'depends' => ['array'],
        'route.*' => ['string']
    ];
}
