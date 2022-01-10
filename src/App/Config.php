<?php

namespace EasyTool\Framework\App;

class Config
{
    use Data\MultiLevelsStructure;

    public const ENV = 'env';
    public const SYSTEM = 'system';

    protected FileManager $fileManager;
    protected array $data = [self::SYSTEM => []];

    public function __construct(
        FileManager $fileManager
    ) {
        $this->fileManager = $fileManager;
    }

    /**
     * Get config data of specified path and namespace
     */
    public function get(?string $path, $namespace = self::SYSTEM)
    {
        if (!isset($this->data[$namespace])) {
            $configFile = $this->fileManager->getDirectoryPath(FileManager::DIR_CONFIG) . '/' . $namespace . '.php';
            $this->data[$namespace] = (is_file($configFile) && is_array(($config = require $configFile)))
                ? $config : [];
        }
        if ($path == null) {
            return $this->data[$namespace];
        }
        return $this->getChildByPath(explode('/', $path), $this->data[$namespace]);
    }

    /**
     * Get environment config
     */
    public function getEnv(?string $path)
    {
        return $this->get($path, self::ENV);
    }

    /**
     * Get config data by specified path of system namespace
     */
    public function set($path, $value): self
    {
        return $this->setChildByPath(explode('/', $path), $this->data[self::SYSTEM], $value);
    }
}
