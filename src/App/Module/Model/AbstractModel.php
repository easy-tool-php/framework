<?php

namespace EasyTool\Framework\App\Module\Model;

use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\ObjectManager;

abstract class AbstractModel extends DataObject
{
    protected ObjectManager $objectManager;
    protected AbstractResource $resource;

    protected array $orgData = [];

    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
        $this->construct();
    }

    protected function beforeSave(): self
    {
        return $this;
    }

    public function save(): self
    {
        $this->beforeSave();
        $this->resource->save($this);
        return $this->afterSave();
    }

    protected function afterSave(): self
    {
        return $this;
    }

    protected function beforeDelete(): self
    {
        return $this;
    }

    public function delete(): self
    {
        $this->beforeDelete();
        $this->resource->delete($this);
        return $this->afterDelete();
    }

    protected function afterDelete(): self
    {
        return $this;
    }

    protected function beforeLoad(): self
    {
        return $this;
    }

    public function load(): self
    {
        $this->beforeLoad();
        $this->resource->load($this);
        $this->orgData = $this->getData();
        return $this->afterLoad();
    }

    protected function afterLoad(): self
    {
        return $this;
    }

    public function getId(): ?int
    {
        return $this->data[$this->resource->getPrimaryKey()] ?? null;
    }

    /**
     * Set class name of resource model
     */
    protected function initialize(string $resourceClass): void
    {
        $this->resource = $this->objectManager->create($resourceClass);
    }

    /**
     * Do initialization
     */
    abstract protected function construct(): void;
}
