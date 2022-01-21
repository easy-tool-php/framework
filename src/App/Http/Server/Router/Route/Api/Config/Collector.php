<?php

namespace EasyTool\Framework\App\Http\Server\Router\Route\Api\Config;

use EasyTool\Framework\App\Config\AbstractCollector;

class Collector extends AbstractCollector
{
    public function validate(array $config): bool
    {
        return true;
    }
}
