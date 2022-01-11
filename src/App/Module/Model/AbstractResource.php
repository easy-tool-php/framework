<?php

namespace EasyTool\Framework\App\Module\Model;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Sql\Sql;

abstract class AbstractResource
{
    protected const DEFAULT_PRIMARY_KEY = 'id';

    protected ConnectionInterface $conn;
    protected Sql $sql;

    protected string $connName;
    protected string $mainTable;
    protected string $primaryKey;

    public function __construct(
        DatabaseManager $databaseManager
    ) {
        $this->construct();
        $this->sql = new Sql($databaseManager->getAdapter($this->connName), $this->mainTable);
        $this->conn = $this->sql->getAdapter()->getDriver()->getConnection();
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function save(AbstractModel $model): self
    {
        try {
            $this->conn->beginTransaction();
            $sql = $model->getId()
                ? $this->sql->update()->where([$model->getPrimaryKey() => $model->getId()])->set($model->getData())
                : $this->sql->insert()->values($model->getData());
            $this->conn->execute($this->sql->buildSqlString($sql));

            $this->conn->commit();
        } catch (PDOException $e) {
            $this->conn->rollback();
            throw $e;
        } catch (Exception $e) {
            $this->conn->rollback();
        }

        return $this;
    }

    public function load(AbstractModel $model, $id, $field = null): self
    {
        $field = $field ?: $this->primaryKey;
        $sql = $this->sql->select()->where([$field => $id]);
        $statement = $this->sql->prepareStatementForSqlObject($sql);
        if (!empty(($data = $statement->execute()->current()))) {
            $model->setData($data);
        }
        return $this;
    }

    public function delete(AbstractModel $model): self
    {
        try {
            $this->conn->beginTransaction();
            $sql = $this->sql->delete()->where([$model->getPrimaryKey() => $model->getId()]);
            $this->conn->execute($this->sql->buildSqlString($sql));

            $this->conn->commit();
        } catch (PDOException $e) {
            $this->conn->rollback();
            throw $e;
        } catch (Exception $e) {
            $this->conn->rollback();
        }

        return $this;
    }

    /**
     * Set class name of resource model
     */
    protected function initialize(
        string $mainTable,
        string $primaryKey = self::DEFAULT_PRIMARY_KEY,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): void {
        $this->connName = $connName;
        $this->mainTable = $mainTable;
        $this->primaryKey = $primaryKey;
    }

    /**
     * Do initialization
     */
    abstract protected function construct(): void;
}
