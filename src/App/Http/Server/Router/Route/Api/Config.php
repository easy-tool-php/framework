<?php

namespace EasyTool\Framework\App\Http\Server\Router\Route\Api;

use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'api.php';
    protected array $format = [];
}
