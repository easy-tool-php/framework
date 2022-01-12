<?php

namespace EasyTool\Framework\App\Module\Model;

use EasyTool\Framework\App\Data\Collection;
use EasyTool\Framework\App\ObjectManager;
use ReflectionClass;

abstract class AbstractCollection extends Collection
{
    protected ObjectManager $objectManager;
    protected Resource $resource;
    protected string $modelClass;

    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->construct();
    }

    /**
     * This method is prepared for entity collection class to build relationship with entity class
     */
    protected function initialize(string $modelClass): void
    {
        $this->modelClass = $modelClass;

        $reflectionClass = new ReflectionClass($modelClass);
        $constants = $reflectionClass->getConstants();
        $this->resource = $this->objectManager->create(Resource::class, [
            'mainTable' => $constants['MAIN_TABLE'],
            'connName' => $constants['CONN_NAME']
        ]);
    }

    public function load(): self
    {
        return $this;
    }

    /**
     * Initialization
     */
    abstract protected function construct(): void;
}
