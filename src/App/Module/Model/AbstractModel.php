<?php

namespace EasyTool\Framework\App\Module\Model;

use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;

abstract class AbstractModel extends DataObject
{
    public const MAIN_TABLE = null;
    public const PRIMARY_KEY = 'id';
    public const CONN_NAME = DatabaseManager::DEFAULT_CONN;

    protected Resource $resource;

    protected array $orgData = [];

    public function __construct(ObjectManager $objectManager)
    {
        $this->resource = $objectManager->create(Resource::class, [
            'mainTable' => static::MAIN_TABLE,
            'connName' => static::CONN_NAME
        ]);
    }

    protected function beforeSave(): self
    {
        return $this;
    }

    protected function afterSave(): self
    {
        return $this;
    }

    protected function beforeDelete(): self
    {
        return $this;
    }

    protected function afterDelete(): self
    {
        return $this;
    }

    protected function beforeLoad(): self
    {
        return $this;
    }

    protected function afterLoad(): self
    {
        return $this;
    }

    public function save()
    {
        $this->beforeSave();
        $this->resource->save($this);
        return $this->afterSave();
    }

    public function delete()
    {
        $this->beforeDelete();
        $this->resource->delete($this);
        return $this->afterDelete();
    }

    public function load()
    {
        $this->beforeLoad();
        $this->resource->load($this, $this->getId());
        return $this->afterLoad();
    }

    public function getId(): ?int
    {
        return $this->data[static::PRIMARY_KEY] ?? null;
    }

    public function getPrimaryKey(): string
    {
        return static::PRIMARY_KEY;
    }

    public static function createCollection(): AbstractCollection
    {
        return ObjectManager::getInstance()->create(static::class . '\\Collection');
    }

    public static function createInstance(): AbstractModel
    {
        return ObjectManager::getInstance()->create(static::class);
    }
}
