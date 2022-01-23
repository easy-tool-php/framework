<?php

namespace EasyTool\Framework\App\Module\Setup;

use EasyTool\Framework\App\Database\Setup as DbSetup;
use EasyTool\Framework\App\Di\Container as DiContainer;

abstract class AbstractSetup
{
    protected DbSetup $databaseSetup;
    protected DiContainer $diContainer;

    public function __construct(
        DbSetup $databaseSetup,
        DiContainer $diContainer
    ) {
        $this->databaseSetup = $databaseSetup;
        $this->diContainer = $diContainer;
    }

    /**
     * Setup script
     */
    abstract public function execute(): void;
}
