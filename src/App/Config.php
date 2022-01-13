<?php

namespace EasyTool\Framework\App;

use EasyTool\Framework\App;
use EasyTool\Framework\Code\Generator\ArrayGenerator;
use Laminas\Code\Generator\FileGenerator;

class Config
{
    use Data\MultiLevelsStructure;

    public const ENV = 'env';
    public const SYSTEM = 'system';

    protected App $app;
    protected ArrayGenerator $arrayGenerator;
    protected FileGenerator $fileGenerator;

    protected array $data = [self::SYSTEM => []];

    public function __construct(
        App $app,
        ArrayGenerator $arrayGenerator,
        FileGenerator $fileGenerator
    ) {
        $this->app = $app;
        $this->arrayGenerator = $arrayGenerator;
        $this->fileGenerator = $fileGenerator;
    }

    /**
     * Get config data of specified path and namespace
     */
    public function get(?string $path, string $namespace = self::SYSTEM)
    {
        if (!isset($this->data[$namespace])) {
            $configFile = $this->app->getDirectoryPath(App::DIR_CONFIG) . '/' . $namespace . '.php';
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
     * Set config data by specified path and namespace
     */
    public function set(?string $path, $value, string $namespace = self::SYSTEM): self
    {
        if ($path == null) {
            $this->data[$namespace] = $value;
            return $this;
        }
        return $this->setChildByPath(explode('/', $path), $this->data[$namespace], $value);
    }

    /**
     * Store config data
     */
    public function save(string $namespace): self
    {
        if ($namespace == self::SYSTEM) {
            return $this->saveSystemConfig();
        }
        $filename = $this->app->getDirectoryPath(App::DIR_CONFIG) . '/' . $namespace . '.php';
        if (!is_dir(($dir = dirname($filename)))) {
            mkdir($dir, 0755, true);
        }
        $this->fileGenerator->setFilename($filename)
            ->setBody('return ' . $this->arrayGenerator->setArray($this->get(null, $namespace))->generate() . ";\n")
            ->write();
        return $this;
    }

    /**
     * Store system config data
     */
    private function saveSystemConfig(): self
    {
        return $this;
    }
}
