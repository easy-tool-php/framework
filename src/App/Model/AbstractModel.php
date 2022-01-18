<?php

namespace EasyTool\Framework\App\Model;

use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\Database\Connection;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;
use Exception;

abstract class AbstractModel extends DataObject
{
    public const MAIN_TABLE = null;
    public const PRIMARY_KEY = 'id';
    public const CONN_NAME = DatabaseManager::DEFAULT_CONN;

    protected Connection $conn;

    protected array $orgData = [];

    public function __construct(ObjectManager $objectManager)
    {
        $this->conn = Connection::createInstance(static::MAIN_TABLE, static::CONN_NAME);
    }

    /**
     * Prepare for saving record
     *
     * This method is executed between begin transaction and commit
     *     so that the data modification can be rollback when meeting exception.
     */
    protected function beforeSave(): self
    {
        return $this;
    }

    /**
     * Do something after saving record
     *
     * This method is executed between begin transaction and commit
     *     so that the data modification can be rollback when meeting exception.
     */
    protected function afterSave(): self
    {
        return $this;
    }

    /**
     * Prepare for removing record
     */
    protected function beforeDelete(): self
    {
        return $this;
    }

    /**
     * Do something after removing record
     */
    protected function afterDelete(): self
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
        try {
            $this->conn->beginTransaction();
            $this->beforeSave();
            $this->getId()
                ? $this->conn->update([static::PRIMARY_KEY => $this->data[static::PRIMARY_KEY]], $this->getData())
                : $this->set(static::PRIMARY_KEY, $this->conn->insert($this->getData()));
            $this->afterSave();
            $this->conn->commit();
        } catch (PDOException $e) {
            $this->conn->rollback();
            throw $e;
        } catch (Exception $e) {
            $this->conn->rollback();
        }
        return $this;
    }

    /**
     * Remove the record
     */
    public function delete()
    {
        $this->beforeDelete();
        $this->conn->delete([static::PRIMARY_KEY => $this->data[static::PRIMARY_KEY]]);
        return $this->afterDelete();
    }

    /**
     * Retrieve the record from database with specified value of primary key
     */
    public function load()
    {
        if (!$this->getId()) {
            throw new Exception('Identifier is not set.');
        }
        $this->beforeLoad();
        $this->conn->getSelect()->where([static::PRIMARY_KEY => $this->data[static::PRIMARY_KEY]]);
        $this->orgData = $this->data = $this->conn->fetchRow();
        return $this->afterLoad();
    }

    /**
     * Returns the initialized resource instance
     */
    public function getConnection(): Resource
    {
        return $this->conn;
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
