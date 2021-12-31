<?php

namespace EasyTool\Framework\App;

class Config extends Data\DataObject
{
    private FileManager $fileManager;

    public function __construct(
        FileManager $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    public function collectConfigByName($name): array
    {
        $configFile = $this->fileManager->getDirectoryPath(FileManager::DIR_CONFIG) . '/' . $name . '.php';
        $this->set($name, (is_file($configFile) && is_array(($config = require $configFile))) ? $config : []);
        return $this->get($name);
    }

    public function updateConfigByName($name, $config): void
    {
    }
}
