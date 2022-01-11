<?php

namespace EasyTool\Framework\App\Database;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Sql\Sql;

class Query implements QueryInterface
{
    protected ConnectionInterface $conn;
    protected Sql $sql;

    public function __construct(
        DatabaseManager $databaseManager,
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ) {
        $this->sql = new Sql($databaseManager->getAdapter($connName), $table);
        $this->conn = $this->sql->getAdapter()->getDriver()->getConnection();
    }

    public function fetchAll(): array
    {
        // TODO: Implement fetchAll() method.
    }

    public function fetchPairs(string $keyColumn, string $valueColumn): array
    {
        // TODO: Implement fetchPairs() method.
    }

    public function fetchColumn(string $column): array
    {
        // TODO: Implement fetchColumn() method.
    }

    public function fetchRow(): array
    {
        // TODO: Implement fetchRow() method.
    }

    public function fetchOne(): string
    {
        // TODO: Implement fetchOne() method.
    }

    public function getAllIds(): array
    {
        // TODO: Implement getAllIds() method.
    }

    public function getConnection(): object
    {
        return$this->conn;
    }

    public function getSize(): int
    {
        // TODO: Implement getSize() method.
    }
}
