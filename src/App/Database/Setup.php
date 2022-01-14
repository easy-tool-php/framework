<?php

namespace EasyTool\Framework\App\Database;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\Database\Setup\Table;
use EasyTool\Framework\App\ObjectManager;

class Setup
{
    private ObjectManager $objectManager;

    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create a new table
     */
    public function createTable(
        string $table,
        string $connName = DatabaseManager::DEFAULT_CONN
    ): Table {
        return $this->objectManager->create(Table::class, [
            'name' => $table,
            'connName' => $connName
        ]);
    }
}
