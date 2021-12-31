<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Data\DataObject;

abstract class Config extends DataObject
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->initialize();
    }

    /**
     * Initializing on creating
     */
    abstract protected function initialize(): void;

    /**
     * Store data of the configuration
     */
    abstract public function save(): Config;
}
