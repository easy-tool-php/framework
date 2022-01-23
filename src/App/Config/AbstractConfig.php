<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\Data\MultiLevelsStructure;
use EasyTool\Framework\Validation\Validator;

abstract class AbstractConfig extends DataObject
{
    use MultiLevelsStructure;

    protected Validator $validator;

    public function __construct(
        Validator $validator,
        array $data = []
    ) {
        $this->validator = $validator;
    }

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

    /**
     * Collect configuration data on creating the instance
     */
    abstract public function collectData();
}
