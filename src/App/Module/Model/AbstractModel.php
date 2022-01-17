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
            'connName'  => static::CONN_NAME
        ]);
    }

    /**
     * Prepare for saving record
     *
     * This method is executed between begin transaction and commit in resource model.
     *
     * @see Resource::save
     */
    public function beforeSave(): self
    {
        return $this;
    }

    /**
     * Do something after saving record
     *
     * This method is executed between begin transaction and commit in resource model.
     *
     * @see Resource::save
     */
    public function afterSave(): self
    {
        return $this;
    }

    /**
     * Prepare for removing record
     *
     * This method is executed between begin transaction and commit in resource model.
     *
     * @see Resource::delete
     */
    public function beforeDelete(): self
    {
        return $this;
    }

    /**
     * Do something after removing record
     *
     * This method is executed between begin transaction and commit in resource model.
     *
     * @see Resource::delete
     */
    public function afterDelete(): self
    {
        return $this;
    }

    /**
     * Prepare for loading
     */
    protected function beforeLoad(): self
    {
        return $this;
    }

    /**
     * Do something after loaded
     */
    protected function afterLoad(): self
    {
        return $this;
    }

    /**
     * Store data of the model in database, create a new record if the value of primary key is not specified
     */
    public function save()
    {
        $this->resource->save($this);
        return $this;
    }

    /**
     * Remove the record
     */
    public function delete()
    {
        $this->resource->delete($this);
        return $this;
    }

    /**
     * Retrieve the record from database with specified value of primary key
     */
    public function load()
    {
        $this->beforeLoad();
        $this->resource->load($this, $this->getId());
        $this->orgData = $this->data;
        return $this->afterLoad();
    }

    /**
     * Get the value of primary key
     */
    public function getId(): ?int
    {
        return $this->data[static::PRIMARY_KEY] ?? null;
    }

    /**
     * Get primary key
     */
    public function getPrimaryKey(): string
    {
        return static::PRIMARY_KEY;
    }

    /**
     * Returns the initialized resource instance
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * Quick way to create a new model instance
     */
    public static function createInstance(): AbstractModel
    {
        return ObjectManager::getInstance()->create(static::class);
    }

    /**
     * Quick way to create a new collection instance
     */
    public static function createCollection(): AbstractCollection
    {
        return ObjectManager::getInstance()->create(static::class . '\\Collection');
    }
}
