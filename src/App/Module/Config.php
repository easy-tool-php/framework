<?php

namespace EasyTool\Framework\App\Module;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'module.php';
    protected array $format = [
        'name'    => ['required'],
        'depends' => ['array'],
        'route'   => ['array', 'options' => [Area::FRONTEND, Area::BACKEND, Area::API]]
    ];
}
