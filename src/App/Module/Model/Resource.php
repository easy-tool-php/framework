<?php

namespace EasyTool\Framework\App\Module\Model;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Sql\Sql;

class Resource
{
    protected ConnectionInterface $conn;
    protected Sql $sql;

    public function __construct(
        DatabaseManager $databaseManager,
        string $mainTable,
        string $connName
    ) {
        $this->sql = new Sql($databaseManager->getAdapter($connName), $mainTable);
        $this->conn = $this->sql->getAdapter()->getDriver()->getConnection();
    }

    /**
     * Save data of specified model
     */
    public function save(AbstractModel $model): self
    {
        try {
            $this->conn->beginTransaction();
            $model->beforeSave();
            $sql = $model->getId()
                ? $this->sql->update()->where([$model->getPrimaryKey() => $model->getId()])->set($model->getData())
                : $this->sql->insert()->values($model->getData());
            $this->conn->execute($this->sql->buildSqlString($sql));
            $model->afterSave();
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
     * Remove record of given model
     */
    public function delete(AbstractModel $model): self
    {
        try {
            $this->conn->beginTransaction();
            $model->beforeDelete();
            $sql = $this->sql->delete()->where([$model->getPrimaryKey() => $model->getId()]);
            $this->conn->execute($this->sql->buildSqlString($sql));
            $model->afterDelete();
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
     * Load data of specified ID and assign to given model
     */
    public function load(AbstractModel $model, $value, $field = null): self
    {
        $field = $field ?: $model->getPrimaryKey();
        $sql = $this->sql->select()->where([$field => $value]);
        $statement = $this->sql->prepareStatementForSqlObject($sql);
        if (!empty(($data = $statement->execute()->current()))) {
            $model->setData($data);
        }
        return $this;
    }

    /**
     * Returns the initialized Sql instance
     */
    public function getSqlProcessor(): Sql
    {
        return $this->sql;
    }
}
