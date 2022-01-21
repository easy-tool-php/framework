<?php

namespace EasyTool\Framework\App\Event\Config;

use EasyTool\Framework\App\Config\AbstractCollector;
use EasyTool\Framework\App\Event\Manager as EventManager;

class Collector extends AbstractCollector
{
    protected string $namespace = EventManager::CONFIG_NAME;

    public function validate(array $config): bool
    {
        return $this->validator->validate(
            [
                '*.*.listener' => ['required', 'string'],
                '*.*.order'    => ['int']
            ],
            $config
        );
    }
}
