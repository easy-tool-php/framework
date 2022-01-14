<?php

namespace EasyTool\Framework\App\Module\Setup;

use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\ObjectManager;

abstract class AbstractSetup
{
    protected DatabaseManager $databaseManager;
    protected ObjectManager $objectManager;

    public function __construct(
        DatabaseManager $databaseManager,
        ObjectManager $objectManager
    ) {
        $this->databaseManager = $databaseManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Upgrading script
     */
    abstract public function upgrade(): void;
}
