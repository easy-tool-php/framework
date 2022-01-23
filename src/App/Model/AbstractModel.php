<?php

namespace EasyTool\Framework\App\Model;

use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\Database\Connection;
use EasyTool\Framework\App\Database\Manager as DbManager;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Di\Container as DiContainer;
use Exception;

abstract class AbstractModel extends DataObject
{
    public const MAIN_TABLE = null;
    public const PRIMARY_KEY = 'id';
    public const CONN_NAME = DbManager::DEFAULT_CONN;

    protected Connection $conn;
    protected EventManager $eventManager;

    protected array $orgData = [];

    public function __construct(EventManager $eventManager)
    {
        $this->conn = Connection::createInstance(static::MAIN_TABLE, static::CONN_NAME);
        $this->eventManager = $eventManager;
    }

    /**
     * Prepare for saving record
     *
     * This method is executed between begin transaction and commit
     *     so that the data modification can be rollback when meeting exception.
     */
    protected function beforeSave(): self
    {
        $this->eventManager->dispatch((new Event('before_model_save'))->set('model', $this));
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
        $this->eventManager->dispatch((new Event('after_model_save'))->set('model', $this));
        return $this;
    }

    /**
     * Prepare for removing record
     */
    protected function beforeDelete(): self
    {
        $this->eventManager->dispatch((new Event('before_model_delete'))->set('model', $this));
        return $this;
    }

    /**
     * Do something after removing record
     */
    protected function afterDelete(): self
    {
        $this->eventManager->dispatch((new Event('after_model_delete'))->set('model', $this));
        return $this;
    }

    /**
     * Prepare for loading
     */
    protected function beforeLoad(): self
    {
        $this->eventManager->dispatch((new Event('before_model_load'))->set('model', $this));
        return $this;
    }

    /**
     * Do something after loaded
     */
    protected function afterLoad()
    {
        $this->eventManager->dispatch((new Event('after_model_load'))->set('model', $this));
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
                : $this->conn->insert($this->getData());
            $this->conn->commit();
            $this->set(static::PRIMARY_KEY, $this->conn->getLastGeneratedValue());
            $this->afterSave();
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
        $this->afterDelete();
        return $this;
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
        $this->afterLoad();
        return $this;
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
        return DiContainer::getInstance()->create(static::class);
    }

    /**
     * Quick way to create a new collection instance
     */
    public static function createCollection(): AbstractCollection
    {
        return DiContainer::getInstance()->create(static::class . '\\Collection');
    }
}
