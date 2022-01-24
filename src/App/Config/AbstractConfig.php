<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\Data\MultiLevelsStructure;

abstract class AbstractConfig extends DataObject
{
    use MultiLevelsStructure;

    /**
     * Get config data of specified path and namespace
     */
    public function get(string $path)
    {
        return $this->getChildByPath(explode('/', $path), $this->data);
    }

    /**
     * Set config data by specified path and namespace
     */
    public function set(string $path, $value): self
    {
        return $this->setChildByPath(explode('/', $path), $this->data, $value);
    }
}
