<?php

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
