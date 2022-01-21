<?php

namespace EasyTool\Framework\App\ObjectManager\Config;

use EasyTool\Framework\App\Config\AbstractCollector;
use EasyTool\Framework\App\ObjectManager;

class Collector extends AbstractCollector
{
    protected string $namespace = ObjectManager::CONFIG_NAME;

    public function validate(array $config): bool
    {
        return true;
    }
}
