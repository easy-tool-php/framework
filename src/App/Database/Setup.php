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
use Laminas\Db\Sql\Ddl\Column\Column;
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
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;

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
    public const COL_COLUMN_FORMAT = 'format';
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
    private function getDdlColumn(array $metadata): Column
    {
        if (
            !$this->validator->validate(
                [
                    self::COL_NAME           => ['required'],
                    self::COL_TYPE           => ['required', 'string', 'options' => array_keys($this->columnTypes)],
                    self::COL_LENGTH         => ['int'],
                    self::COL_NULLABLE       => ['bool'],
                    self::COL_UNSIGNED       => ['bool'],
                    self::COL_AUTO_INCREMENT => ['bool'],
                    self::COL_ZEROFILL       => ['bool'],
                    self::COL_COMMENT        => ['string']
                ],
                $metadata
            )
        ) {
            throw new InvalidArgumentException('Invalid attribute format.');
        }

        $name = $metadata[self::COL_NAME];
        $type = $metadata[self::COL_TYPE];
        $length = $metadata[self::COL_LENGTH] ?? null;
        $nullable = $metadata[self::COL_NULLABLE] ?? null;
        $default = $metadata[self::COL_DEFAULT] ?? null;

        return $this->objectManager->create($this->columnTypes[$type], [
            'name'     => $name,
            'length'   => $length,
            'nullable' => $nullable,
            'default'  => $default,
            'options'  => $metadata
        ]);
    }

    /**
     * Create a new table
     */
    public function createTable(
        string $table,
        array $columns,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var CreateTable $sql */
        $sql = $this->objectManager->create(CreateTable::class, ['table' => $table]);
        foreach ($columns as $column) {
            $sql->addColumn($this->getDdlColumn($column));
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
        string $column,
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): self {
        /** @var AlterTable $sql */
        $sql = $this->objectManager->create(AlterTable::class, ['table' => $table]);
        $sql->dropColumn($column);
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
