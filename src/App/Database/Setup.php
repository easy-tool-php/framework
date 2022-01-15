<?php

namespace EasyTool\Framework\App\Database;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Validation\Validator;
use Laminas\Db\Metadata\MetadataInterface;
use Laminas\Db\Metadata\Source\Factory;
use Laminas\Db\Sql\AbstractSql;
use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\CreateTable;

class Setup
{
    public const COL_NAME = 'name';
    public const COL_NULLABLE = 'nullable';
    public const COL_DEFAULT = 'default';
    public const COL_TYPE = 'type';

    private DatabaseManager $databaseManager;
    private ObjectManager $objectManager;
    private Validator $validator;

    private array $sources = [];

    public function __construct(
        DatabaseManager $databaseManager,
        ObjectManager $objectManager,
        Validator $validator
    ) {
        $this->databaseManager = $databaseManager;
        $this->objectManager = $objectManager;
        $this->validator = $validator;
    }

    /**
     * Execute SQL with given connection
     */
    private function execute(AbstractSql $sql, string $connName): void
    {
        $adapter = $this->databaseManager->getAdapter($connName);
        $statement = $adapter->getDriver()->createStatement(
            $sql->getSqlString($adapter->getPlatform())
        );
        $statement->execute();
    }

    /**
     * Check whether the given metadata has right format for a column
     */
    private function getDdlColumn(array $metadata): Column
    {
        if (
            !$this->validator->validate(
                [
                    self::COL_NAME => ['required'],
                    self::COL_NULLABLE => ['bool'],
                    self::COL_TYPE => ['string']
                ],
                $metadata
            )
        ) {
            throw new InvalidArgumentException('Invalid attribute format.');
        }

        $name = $metadata[self::COL_NAME];
        $nullable = $metadata[self::COL_NULLABLE] ?? null;
        $default = $metadata[self::COL_DEFAULT] ?? null;
        unset($metadata[self::COL_NAME], $metadata[self::COL_NULLABLE], $metadata[self::COL_DEFAULT]);

        return $this->objectManager->create(Column::class, [
            'name' => $name,
            'nullable' => $nullable,
            'default' => $default,
            'options' => $metadata
        ]);
    }

    /**
     * Create a new table
     */
    public function createTable(
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var CreateTable $sql */
        $sql = $this->objectManager->create(CreateTable::class, ['table' => $table]);
        $this->execute($sql, $connName);
        return $this;
    }

    /**
     * Add a new column with given metadata into specified table
     */
    public function addColumn(
        array $metadata,
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var AlterTable $sql */
        $sql = $this->objectManager->create(AlterTable::class, ['table' => $table]);
        $sql->addColumn($this->getDdlColumn($metadata));
        $this->execute($sql, $connName);
        return $this;
    }

    /**
     * Check whether specified table exists with given connection
     */
    public function isTableExist(
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): bool {
        return in_array($table, $this->getSource($connName)->getTableNames());
    }

    /**
     * Get source of specified connection
     */
    public function getSource(string $connName = DatabaseManager::DEFAULT_CONN): MetadataInterface
    {
        if (!isset($this->sources[$connName])) {
            $this->sources[$connName] = Factory::createSourceFromAdapter(
                $this->databaseManager->getAdapter($connName)
            );
        }
        return $this->sources[$connName];
    }
}
