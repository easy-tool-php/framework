<?php

namespace EasyTool\Framework\App\Database;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;
use Exception;
use InvalidArgumentException;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Laminas\Db\Sql\Ddl\SqlInterface;
use Laminas\Db\Sql\PreparableSqlInterface;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;

class Connection
{
    protected ConnectionInterface $conn;
    protected Select $select;
    protected Sql $sql;

    protected ?array $result = null;

    public function __construct(
        DatabaseManager $databaseManager,
        ObjectManager $objectManager,
        ?string $mainTable = null,
        string $connName = DatabaseManager::DEFAULT_CONN
    ) {
        $this->sql = $objectManager->create(Sql::class, [
            'adapter' => $databaseManager->getAdapter($connName),
            'table'   => $mainTable
        ]);
        $this->select = $this->sql->select();
        $this->conn = $this->sql->getAdapter()->getDriver()->getConnection();
    }

    /**
     * Execute the query
     */
    private function query(): void
    {
        $this->result = [];
        foreach ($this->sql->prepareStatementForSqlObject($this->select)->execute() as $rowData) {
            $this->result[] = $rowData;
        }
    }

    /**
     * Create a new record with given data in specified table
     */
    public function insert(array $data): int
    {
        $sql = $this->sql->insert()->values($data);
        $this->conn->execute($this->sql->buildSqlString($sql));
        return $this->conn->getLastGeneratedValue();
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
     * Begin transaction
     */
    public function beginTransaction(): self
    {
        $this->conn->beginTransaction();
        return $this;
    }

    /**
     * Commit transaction
     */
    public function commit(): self
    {
        $this->conn->commit();
        return $this;
    }

    /**
     * Rollback
     */
    public function rollback(): self
    {
        $this->conn->rollback();
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
            $this->query();
        }
        return $this->result;
    }

    /**
     * Retrieve all matched records with the first 2 columns and assign them as an [key => value] format array.
     */
    public function fetchPair(): array
    {
        if ($this->result === null) {
            $this->query();
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
            $this->query();
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
            $this->query();
        }
        return empty($this->result) ? null : $this->result[0];
    }

    /**
     * Retrieve the first column of the first matched record.
     */
    public function fetchOne(): ?string
    {
        if ($this->result === null) {
            $this->query();
        }
        return empty($this->result) ? null : reset($this->result[0]);
    }

    /**
     * Returns the Select instance
     */
    public function getSelect(): Select
    {
        return $this->select;
    }

    /**
     * Returns the initialized Sql instance
     */
    public function getSqlProcessor(): Sql
    {
        return $this->sql;
    }

    /**
     * Execute a SQL
     *
     * @param PreparableSqlInterface|SqlInterface|string $sql
     * @throws Exception
     */
    public function execute($sql): ResultInterface
    {
        if ($sql instanceof PreparableSqlInterface) {
            $statement = $this->sql->prepareStatementForSqlObject($sql);
        } elseif ($sql instanceof SqlInterface) {
            /** @var SqlInterface $sql */
            $statement = $this->sql->getAdapter()->getDriver()->createStatement($sql->getSqlString());
        } elseif (is_string($sql)) {
            $statement = $this->sql->getAdapter()->getDriver()->createStatement($sql);
        } else {
            throw new InvalidArgumentException('Invalid SQL argument.');
        }

        try {
            return $statement->execute();
        } catch (InvalidQueryException $e) {
            throw new InvalidQueryException(
                sprintf("Meet exception:\n%s.\n\nThe SQL is:\n%s", $e->getMessage(), $statement->getSql())
            );
        }
    }

    /**
     * Returns a new connection instance
     */
    public static function createInstance(
        ?string $mainTable,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        return ObjectManager::getInstance()->create(self::class, [
            'mainTable' => $mainTable,
            'connName'  => $connName
        ]);
    }
}
