<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Model;

use ArrayIterator;
use EasyTool\Framework\App\Data\Collection;
use EasyTool\Framework\App\Database\Connection;
use EasyTool\Framework\App\Di\Container as DiContainer;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\TableIdentifier;
use ReflectionClass;

/**
 * @method self from(string|array|TableIdentifier $table)
 * @method self quantifier(string|Expression $quantifier)
 * @method self columns(array $columns, bool $prefixColumnsWithTable = true)
 * @method self join($name, $on, $columns = Select::SQL_STAR, $type = Select::JOIN_INNER)
 * @method self where($predicate, $combination = PredicateSet::OP_AND)
 * @method self group($group)
 * @method self having($predicate, $combination = PredicateSet::OP_AND)
 * @method self order(string|array|Expression $order)
 * @method self limit(int $limit)
 * @method self offset(int $offset)
 * @method self combine(Select $select, $type = Select::COMBINE_UNION, $modifier = '')
 * @method self reset(string $part)
 *
 * @see \Laminas\Db\Sql\Select
 */
abstract class AbstractCollection extends Collection
{
    protected Connection $conn;
    protected DiContainer $diContainer;
    protected Select $select;
    protected bool $loaded = false;
    protected string $modelClass;

    public function __construct(
        DiContainer $diContainer
    ) {
        $this->diContainer = $diContainer;
        $this->construct();
    }

    /**
     * This method is prepared for entity collection class to build relationship with entity class
     */
    protected function initialize(string $modelClass): void
    {
        $reflectionClass = new ReflectionClass($modelClass);
        $constants = $reflectionClass->getConstants();
        $this->conn = Connection::createInstance($constants['MAIN_TABLE'], $constants['CONN_NAME']);
        $this->modelClass = $modelClass;
        $this->select = $this->conn->getSqlProcessor()->select();
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
     * Returns the Select instance
     */
    public function getSelect(): Select
    {
        return $this->select;
    }

    /**
     * Get size of the collection without paging
     */
    public function getSize(): int
    {
        $select = clone $this->select;
        $select->reset(Select::COLUMNS)->columns(['count' => new Expression('COUNT(*)')]);
        $statement = $this->conn->getSqlProcessor()->prepareStatementForSqlObject($select);
        return $statement->execute()->current()['count'];
    }

    /**
     * Retrieve all matched records from database and assign to new model instances
     */
    public function load(): self
    {
        if ($this->loaded) {
            return $this;
        }

        $this->beforeLoad();
        $this->items = [];
        foreach ($this->conn->fetchAll() as $rowData) {
            /** @var AbstractModel $model */
            $model = $this->diContainer->create($this->modelClass);
            $model->setData($rowData);
            $this->items[$model->getId()] = $model;
        }
        $this->loaded = true;
        return $this->afterLoad();
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        $this->load();
        return parent::getIterator();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        $this->load();
        return parent::count();
    }

    /**
     * Try to retrieve from the Select instance when an undefined method is called
     */
    public function __call($name, $arguments): self
    {
        call_user_func_array([$this->select, $name], $arguments);
        return $this;
    }

    /**
     * Initialization
     *
     * The `initialize` method should be executed
     *     to specify related model class and build connection instance.
     */
    abstract protected function construct(): void;
}
