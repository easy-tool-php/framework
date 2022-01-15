<?php

namespace EasyTool\Framework\App\Module\Setup;

use EasyTool\Framework\App\Database\Setup as DatabaseSetup;
use EasyTool\Framework\App\ObjectManager;

abstract class AbstractSetup
{
    protected DatabaseSetup $databaseSetup;
    protected ObjectManager $objectManager;

    public function __construct(
        DatabaseSetup $databaseSetup,
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
