<?php

namespace EasyTool\Framework\App\Http\Server\Request;

use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'middlewares.php';
    protected array $format = [];
}
