<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\ObjectManager;

class Manager
{
    private ObjectManager $objectManager;
    private array $configs = [];

    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function getConfig(string $name, $class = AppConfig::class): Config
    {
        if (!isset($this->configs[$name])) {
            $this->configs[$name] = $this->objectManager->create($class, ['name' => $name]);
        }
        return $this->configs[$name];
    }
}
