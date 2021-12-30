<?php

namespace EasyTool\Framework\App;

use EasyTool\Framework\App\Filesystem\DirectoryManager;

class Config extends Data\DataObject
{
    private DirectoryManager $directoryManager;

    public function __construct(
        DirectoryManager $directoryManager
    ) {
        $this->directoryManager = $directoryManager;
    }

    public function collectConfigByName($name): array
    {
        $configFile = $this->directoryManager->getAbsolutePath(DirectoryManager::CONFIG) . '/' . $name . '.php';
        $this->set($name, (is_file($configFile) && is_array(($config = require $configFile))) ? $config : []);
        return $this->get($name);
    }
}
