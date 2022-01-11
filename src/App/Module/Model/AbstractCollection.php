<?php

namespace EasyTool\Framework\App\Module\Model;

use EasyTool\Framework\App\Data\Collection;
use EasyTool\Framework\App\ObjectManager;

abstract class AbstractCollection extends Collection
{
    protected ObjectManager $objectManager;
    protected AbstractResource $resource;

    protected string $modelClass;

    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->construct();
    }

    public function load(): self
    {
        return $this;
    }

    /**
     * Set class name of resource model
     */
    protected function initialize(string $resourceClass, string $modelClass): void
    {
        $this->modelClass = $modelClass;
        $this->resource = $this->objectManager->create($resourceClass);
    }

    /**
     * Do initialization
     */
    abstract protected function construct(): void;
}
