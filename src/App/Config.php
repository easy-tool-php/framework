<?php

namespace EasyTool\Framework\App;

use EasyTool\Framework\App;

class Config
{
    use Data\MultiLevelsStructure;

    public const ENV = 'env';
    public const SYSTEM = 'system';

    protected App $app;
    protected array $data = [self::SYSTEM => []];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Get config data of specified path and namespace
     */
    public function get(?string $path, string $namespace = self::SYSTEM)
    {
        if (!isset($this->data[$namespace])) {
            $this->data[$namespace] = [];
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
}
