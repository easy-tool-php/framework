<?php

namespace EasyTool\Framework\App\Database\Setup;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Validation\Validator;
use InvalidArgumentException;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Ddl\Column\Column;
use Laminas\Db\Sql\Ddl\CreateTable;

class Table
{
    public const COL_NAME = 'name';
    public const COL_NULLABLE = 'nullable';
    public const COL_DEFAULT = 'default';
    public const COL_TYPE = 'type';

    private Adapter $adapter;
    private CreateTable $sql;
    private ObjectManager $objectManager;
    private Validator $validator;

    public function __construct(
        DatabaseManager $databaseManager,
        ObjectManager $objectManager,
        Validator $validator,
        string $name,
        string $connName = DatabaseManager::DEFAULT_CONN
    ) {
        $this->adapter = $databaseManager->getAdapter($connName);
        $this->objectManager = $objectManager;
        $this->validator = $validator;
        $this->sql = $this->objectManager->create(CreateTable::class, ['table' => $name]);
    }

    /**
     * Add column with given attributes
     */
    public function addColumn($attributes): self
    {
        if (
            !$this->validator->validate(
                [
                    self::COL_NAME => ['required'],
                    self::COL_NULLABLE => ['bool'],
                    self::COL_TYPE => ['string']
                ],
                $attributes
            )
        ) {
            throw new InvalidArgumentException('Invalid attribute format.');
        }
        $this->sql->addColumn(
            $this->objectManager->create(Column::class, [
                'name' => $attributes[self::COL_NAME],
                'nullable' => $attributes[self::COL_NULLABLE] ?? null,
                'default' => $attributes[self::COL_DEFAULT] ?? null,
                'options' => $attributes ?? []
            ])
        );
        return $this;
    }

    /**
     * Do creating
     */
    public function process()
    {
        $statement = $this->adapter->getDriver()->createStatement(
            $this->sql->getSqlString($this->adapter->getPlatform())
        );
        $statement->execute();
    }
}
