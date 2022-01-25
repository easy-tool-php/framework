<?php

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
