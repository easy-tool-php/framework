<?php

namespace EasyTool\Framework\App\Module\Setup;

use EasyTool\Framework\App\Database\Setup as DbSetup;
use EasyTool\Framework\App\ObjectManager;

abstract class AbstractSetup
{
    protected DbSetup $databaseSetup;
    protected ObjectManager $objectManager;

    public function __construct(
        DbSetup $databaseSetup,
        ObjectManager $objectManager
    ) {
        $this->databaseSetup = $databaseSetup;
        $this->objectManager = $objectManager;
    }

    /**
     * Setup script
     */
    abstract public function execute(): void;
}
