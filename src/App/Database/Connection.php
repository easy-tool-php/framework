<?php

namespace EasyTool\Framework\App\Database;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;
use Exception;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;

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
 *
 * @see \Laminas\Db\Sql\Select
 */
class Connection
{
    protected ConnectionInterface $conn;
    protected Select $select;
    protected Sql $sql;

    protected ?array $result = null;

    public function __construct(
        DatabaseManager $databaseManager,
        string $mainTable,
        string $connName = DatabaseManager::DEFAULT_CONN
    ) {
        $this->sql = new Sql($databaseManager->getAdapter($connName), $mainTable);
        $this->select = $this->sql->select();
        $this->conn = $this->sql->getAdapter()->getDriver()->getConnection();
    }

    /**
     * Execute the query
     */
    private function execute(): void
    {
        $this->result = [];
        foreach ($this->sql->prepareStatementForSqlObject($this->select)->execute() as $rowData) {
            $this->result[] = $rowData;
        }
    }

    /**
     * Create a new record with given data in specified table
     */
    public function insert(array $data): self
    {
        $sql = $this->sql->insert()->values($data);
        $this->conn->execute($this->sql->buildSqlString($sql));
        return $this;
    }

    /**
     * Update a record with given data in specified table
     */
    public function update(array $where, array $data): self
    {
        $sql = $this->sql->update()->where($where)->set($data);
        $this->conn->execute($this->sql->buildSqlString($sql));
        return $this;
    }

    /**
     * Remove a record by given condition in specified table
     */
    public function delete(array $where): self
    {
        $sql = $this->sql->delete()->where($where);
        $this->conn->execute($this->sql->buildSqlString($sql));
        return $this;
    }

    /**
     * Retrieve all matched records
     */
    public function fetchAll(): array
    {
        if ($this->result === null) {
            $this->execute();
        }
        return $this->result;
    }

    /**
     * Retrieve all matched records with the first 2 columns and assign them as an [key => value] format array.
     */
    public function fetchPair(): array
    {
        if ($this->result === null) {
            $this->execute();
        }
        if (empty($this->result)) {
            return [];
        }
        [$keyCol, $valCol] = array_pad(array_keys($this->result[0]), 2, null);
        if ($keyCol === null || $valCol === null) {
            throw new Exception('Not enough columns for fetching the result.');
        }
        $result = [];
        foreach ($this->result as $row) {
            $result[$row[$keyCol]] = $row[$valCol];
        }
        return $result;
    }

    /**
     * Retrieve all matched records with the first column.
     */
    public function fetchCol(): array
    {
        if ($this->result === null) {
            $this->execute();
        }
        $result = [];
        foreach ($this->result as $row) {
            $result[] = reset($row);
        }
        return $result;
    }

    /**
     * Retrieve the first record of matched result.
     */
    public function fetchRow(): ?array
    {
        if ($this->result === null) {
            $this->execute();
        }
        return empty($this->result) ? null : $this->result[0];
    }

    /**
     * Retrieve the first column of the first matched record.
     */
    public function fetchOne(): ?string
    {
        if ($this->result === null) {
            $this->execute();
        }
        return empty($this->result) ? null : reset($this->result[0]);
    }

    /**
     * Returns the Select instance
     */
    public function getSelect()
    {
        return $this->select;
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
     * Returns a new connection instance
     */
    public static function createInstance(
        string $mainTable,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        return ObjectManager::getInstance()->create(self::class, [
            'mainTable' => $mainTable,
            'connName'  => $connName
        ]);
    }
}