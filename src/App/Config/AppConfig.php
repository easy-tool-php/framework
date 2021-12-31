<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\FileManager;
use EasyTool\Framework\Code\Generator\ArrayGenerator;
use Laminas\Code\Generator\FileGenerator;

class AppConfig extends Config
{
    private FileManager $fileManager;

    public function __construct(
        FileManager $fileManager,
        string $name
    ) {
        $this->fileManager = $fileManager;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function initialize(): void
    {
        $configFile = $this->fileManager->getDirectoryPath(FileManager::DIR_CONFIG) . '/' . $this->name . '.php';
        $this->setData((is_file($configFile) && is_array(($config = require $configFile))) ? $config : []);
    }

    /**
     * @inheritDoc
     */
    public function save(): AppConfig
    {
        $configFile = $this->fileManager->getDirectoryPath(FileManager::DIR_CONFIG) . '/' . $this->name . '.php';
        FileGenerator::fromArray(['filename' => $configFile])
            ->setBody('return ' . ArrayGenerator::fromArray($this->getData())->generate() . ";\n")
            ->write();
        return $this;
    }
}
