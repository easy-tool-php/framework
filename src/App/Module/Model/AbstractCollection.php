<?php

namespace EasyTool\Framework\App\Module\Model;

use EasyTool\Framework\App\Data\Collection;
use EasyTool\Framework\App\ObjectManager;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\TableIdentifier;
use ReflectionClass;

/**
 * @method from(string|array|TableIdentifier $table)
 * @method quantifier(string|Expression $quantifier)
 * @method columns(array $columns, bool $prefixColumnsWithTable = true)
 * @method join($name, $on, $columns = Select::SQL_STAR, $type = Select::JOIN_INNER)
 * @method where($predicate, $combination = PredicateSet::OP_AND)
 * @method group($group)
 * @method having($predicate, $combination = PredicateSet::OP_AND)
 * @method order(string|array|Expression $order)
 * @method limit(int $limit)
 * @method offset(int $offset)
 * @method combine(Select $select, $type = Select::COMBINE_UNION, $modifier = '')
 * @method reset(string $part)
 * @method setSpecification(string $index, string|array $specification)
 * @method getRawState(?string $key = null)
 * @method isTableReadOnly()
 *
 * @see \Laminas\Db\Sql\Select
 */
abstract class AbstractCollection extends Collection
{
    protected ObjectManager $objectManager;
    protected Resource $resource;
    protected Select $select;
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

        $this->select = $this->resource->getSqlProcessor()->select();
    }

    public function getSelect(): Select
    {
        return $this->select;
    }

    public function getSize(): int
    {
        $select = clone $this->select;
        $select->reset(Select::COLUMNS);
        $select->columns([new Expression('COUNT(*)')]);
        $statement = $this->resource->getSqlProcessor()->prepareStatementForSqlObject($select);
        return $statement->execute()->current()[0];
    }

    public function load(): self
    {
        $this->items = [];
        $statement = $this->resource->getSqlProcessor()->prepareStatementForSqlObject($this->select);
        foreach ($statement->execute() as $rowData) {
            /** @var AbstractModel $model */
            $model = $this->objectManager->create($this->modelClass);
            $model->setData($rowData);
            $this->items[$model->getId()] = $model;
        }
        return $this;
    }

    public function __call($name, $arguments): self
    {
        call_user_func_array([$this->select, $name], $arguments);
        return $this;
    }

    /**
     * Initialization
     */
    abstract protected function construct(): void;
}