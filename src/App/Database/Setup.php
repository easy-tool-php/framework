<?php

namespace EasyTool\Framework\App\Database;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Validation\Validator;
use InvalidArgumentException;
use Laminas\Db\Metadata\MetadataInterface;
use Laminas\Db\Metadata\Source\Factory;
use Laminas\Db\Sql\AbstractSql;
use Laminas\Db\Sql\Ddl\AlterTable;
use Laminas\Db\Sql\Ddl\Column\BigInteger;
use Laminas\Db\Sql\Ddl\Column\Binary;
use Laminas\Db\Sql\Ddl\Column\Blob;
use Laminas\Db\Sql\Ddl\Column\Boolean;
use Laminas\Db\Sql\Ddl\Column\Char;
use Laminas\Db\Sql\Ddl\Column\ColumnInterface;
use Laminas\Db\Sql\Ddl\Column\Date;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Decimal;
use Laminas\Db\Sql\Ddl\Column\Floating;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Text;
use Laminas\Db\Sql\Ddl\Column\Time;
use Laminas\Db\Sql\Ddl\Column\Timestamp;
use Laminas\Db\Sql\Ddl\Column\Varbinary;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\ConstraintInterface;
use Laminas\Db\Sql\Ddl\Constraint\ForeignKey;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Index\Index;

class Setup
{
    public const COL_NAME = 'name';
    public const COL_TYPE = 'data_type';
    public const COL_NULLABLE = 'nullable';
    public const COL_DEFAULT = 'default';
    public const COL_LENGTH = 'length';
    public const COL_UNSIGNED = 'unsigned';
    public const COL_AUTO_INCREMENT = 'identity';
    public const COL_ZEROFILL = 'zerofill';
    public const COL_ON_UPDATE = 'on_update';
    public const COL_FORMAT = 'format';
    public const COL_STORAGE = 'storage';
    public const COL_COMMENT = 'comment';

    public const COL_TYPE_INTEGER = 'INTEGER';
    public const COL_TYPE_BIGINT = 'BIGINT';
    public const COL_TYPE_FLOAT = 'FLOAT';
    public const COL_TYPE_DECIMAL = 'DECIMAL';
    public const COL_TYPE_BINARY = 'BINARY';
    public const COL_TYPE_VARBINARY = 'VARBINARY';
    public const COL_TYPE_BOOLEAN = 'BOOLEAN';
    public const COL_TYPE_DATE = 'DATE';
    public const COL_TYPE_TIME = 'TIME';
    public const COL_TYPE_DATETIME = 'DATETIME';
    public const COL_TYPE_TIMESTAMP = 'TIMESTAMP';
    public const COL_TYPE_CHAR = 'CHAR';
    public const COL_TYPE_VARCHAR = 'VARCHAR';
    public const COL_TYPE_TEXT = 'TEXT';
    public const COL_TYPE_BLOB = 'BLOB';

    public const INDEX_NAME = 'name';
    public const INDEX_TYPE = 'type';
    public const INDEX_COLUMNS = 'columns';
    public const INDEX_LENGTHS = 'lengths';
    public const INDEX_REF_TABLE = 'reference_table';
    public const INDEX_REF_COLUMN = 'reference_column';
    public const INDEX_DELETE_RULE = 'on_delete_rule';
    public const INDEX_UPDATE_RULE = 'on_update_rule';

    public const INDEX_TYPE_BTREE = 'btree';
    public const INDEX_TYPE_FOREIGN = 'foreign';
    public const INDEX_TYPE_PRIMARY = 'primary';
    public const INDEX_TYPE_UNIQUE = 'unique';

    private DatabaseManager $databaseManager;
    private ObjectManager $objectManager;
    private Validator $validator;

    private array $sources = [];

    private array $columnTypes = [
        self::COL_TYPE_INTEGER   => Integer::class,
        self::COL_TYPE_BIGINT    => BigInteger::class,
        self::COL_TYPE_FLOAT     => Floating::class,
        self::COL_TYPE_DECIMAL   => Decimal::class,
        self::COL_TYPE_BINARY    => Binary::class,
        self::COL_TYPE_VARBINARY => Varbinary::class,
        self::COL_TYPE_BOOLEAN   => Boolean::class,
        self::COL_TYPE_DATE      => Date::class,
        self::COL_TYPE_TIME      => Time::class,
        self::COL_TYPE_DATETIME  => Datetime::class,
        self::COL_TYPE_TIMESTAMP => Timestamp::class,
        self::COL_TYPE_CHAR      => Char::class,
        self::COL_TYPE_VARCHAR   => Varchar::class,
        self::COL_TYPE_TEXT      => Text::class,
        self::COL_TYPE_BLOB      => Blob::class
    ];

    private array $indexTypes = [
        self::INDEX_TYPE_BTREE   => Index::class,
        self::INDEX_TYPE_FOREIGN => ForeignKey::class,
        self::INDEX_TYPE_PRIMARY => PrimaryKey::class,
        self::INDEX_TYPE_UNIQUE  => UniqueKey::class,
    ];

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
        $conn = Connection::createInstance(null, $connName);
        $conn->execute($conn->getSqlProcessor()->getSqlPlatform()->getTypeDecorator($sql));
    }

    /**
     * Check whether the given metadata has right format for a column
     */
    private function getDdlColumn(array $metadata): ColumnInterface
    {
        if (
            !$this->validator->validate(
                [
                    self::COL_NAME           => ['required', 'string'],
                    self::COL_TYPE           => ['required', 'string', 'options' => array_keys($this->columnTypes)],
                    self::COL_LENGTH         => ['int'],
                    self::COL_NULLABLE       => ['bool'],
                    self::COL_UNSIGNED       => ['bool'],
                    self::COL_AUTO_INCREMENT => ['bool'],
                    self::COL_ZEROFILL       => ['bool'],
                    self::COL_ON_UPDATE      => ['bool'],
                    self::COL_COMMENT        => ['string']
                ],
                $metadata
            )
        ) {
            throw new InvalidArgumentException('Invalid column metadata.');
        }
        return $this->objectManager->create($this->columnTypes[$metadata[self::COL_TYPE]], [
            'name'     => $metadata[self::COL_NAME],
            'length'   => $metadata[self::COL_LENGTH] ?? null,
            'nullable' => $metadata[self::COL_NULLABLE] ?? null,
            'default'  => $metadata[self::COL_DEFAULT] ?? null,
            'options'  => $metadata
        ]);
    }

    /**
     * Check whether the given metadata has right format for an index
     */
    private function getDdlIndex($metadata): ConstraintInterface
    {
        if (
            !$this->validator->validate(
                [
                    self::INDEX_NAME        => ['string'],
                    self::INDEX_TYPE        => ['required', 'string', 'options' => array_keys($this->indexTypes)],
                    self::INDEX_COLUMNS     => ['required', 'array'],
                    self::INDEX_LENGTHS     => ['array'],
                    self::INDEX_REF_TABLE   => ['string'],
                    self::INDEX_REF_COLUMN  => ['string'],
                    self::INDEX_DELETE_RULE => ['string'],
                    self::INDEX_UPDATE_RULE => ['string']
                ],
                $metadata
            )
        ) {
            throw new InvalidArgumentException('Invalid index metadata.');
        }
        if (empty($metadata[self::INDEX_NAME])) {
            $metadata[self::INDEX_NAME] = strtoupper(
                $metadata[self::INDEX_TYPE] . '_' . implode('_', $metadata[self::INDEX_COLUMNS])
            );
        }
        return $this->objectManager->create($this->indexTypes[$metadata[self::INDEX_TYPE]], [
            'name'            => $metadata[self::INDEX_NAME],
            'columns'         => $metadata[self::INDEX_COLUMNS],
            'referenceTable'  => $metadata[self::INDEX_REF_TABLE] ?? null,
            'referenceColumn' => $metadata[self::INDEX_REF_COLUMN] ?? null,
            'onDeleteRule'    => $metadata[self::INDEX_DELETE_RULE] ?? null,
            'onUpdateRule'    => $metadata[self::INDEX_UPDATE_RULE] ?? null
        ]);
    }

    /**
     * Create a new table
     */
    public function createTable(
        string $table,
        array $columns,
        array $indexes = [],
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var CreateTable $sql */
        $sql = $this->objectManager->create(CreateTable::class, ['table' => $table]);
        foreach ($columns as $column) {
            $sql->addColumn($this->getDdlColumn($column));
            if (!empty($column[self::COL_AUTO_INCREMENT])) {
                $sql->addConstraint(
                    $this->getDdlIndex(
                        [
                            self::INDEX_NAME    => strtoupper($column[self::COL_NAME]),
                            self::INDEX_TYPE    => self::INDEX_TYPE_PRIMARY,
                            self::INDEX_COLUMNS => [$column[self::COL_NAME]]
                        ]
                    )
                );
            }
        }
        foreach ($indexes as $index) {
            $sql->addConstraint($this->getDdlIndex($index));
        }
        $this->execute($sql, $connName);
        return $this;
    }

    /**
     * Drop a table
     */
    public function dropTable(
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var DropTable $sql */
        $sql = $this->objectManager->create(DropTable::class, ['table' => $table]);
        $this->execute($sql, $connName);
        return $this;
    }

    /**
     * Create a new column with given metadata into specified table
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
     * Drop a column in specified table
     */
    public function dropColumn(
        string $columnName,
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var AlterTable $sql */
        $sql = $this->objectManager->create(AlterTable::class, ['table' => $table]);
        $sql->dropColumn($columnName);
        $this->execute($sql, $connName);
        return $this;
    }

    /**
     * Add a new index with given metadata into specified table
     */
    public function addIndex(
        array $metadata,
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var AlterTable $sql */
        $sql = $this->objectManager->create(AlterTable::class, ['table' => $table]);
        $sql->addConstraint($this->getDdlIndex($metadata));
        $this->execute($sql, $connName);
        return $this;
    }

    /**
     * Drop an index in specified table
     */
    public function dropIndex(
        string $indexName,
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var AlterTable $sql */
        $sql = $this->objectManager->create(AlterTable::class, ['table' => $table]);
        $sql->dropConstraint($indexName);
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
    public function getSource(
        string $connName = DatabaseManager::DEFAULT_CONN
    ): MetadataInterface {
        if (!isset($this->sources[$connName])) {
            $this->sources[$connName] = Factory::createSourceFromAdapter(
                $this->databaseManager->getAdapter($connName)
            );
        }
        return $this->sources[$connName];
    }
}
