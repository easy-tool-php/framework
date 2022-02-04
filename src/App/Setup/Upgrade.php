<?php

namespace EasyTool\Framework\App\Setup;

use EasyTool\Framework\App;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Database\Connection;
use EasyTool\Framework\App\Database\Manager as DbManager;
use EasyTool\Framework\App\Database\Setup as DbSetup;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\Module\Setup\AbstractSetup;
use Laminas\Code\Scanner\DirectoryScanner;
use ReflectionClass;

class Upgrade
{
    public const DB_TABLE = 'executed_setups';

    private App $app;
    private CacheManager $cacheManager;
    private DbManager $dbManager;
    private DbSetup $dbSetup;
    private Directory $directory;
    private ModuleManager $moduleManager;
    private DiContainer $diContainer;

    public function __construct(
        App $app,
        CacheManager $cacheManager,
        DbManager $dbManager,
        DbSetup $dbSetup,
        Directory $directory,
        ModuleManager $moduleManager,
        DiContainer $diContainer
    ) {
        $this->app = $app;
        $this->cacheManager = $cacheManager;
        $this->dbManager = $dbManager;
        $this->dbSetup = $dbSetup;
        $this->directory = $directory;
        $this->moduleManager = $moduleManager;
        $this->diContainer = $diContainer;
    }

    /**
     * Check whether the setup table is created
     *
     * Setup table is used to store names of the setup scripts which has been executed,
     *     so that to skip them at next upgrading.
     */
    private function checkSetupTable()
    {
        if (!$this->dbSetup->isTableExist(self::DB_TABLE)) {
            $this->dbSetup->createTable(self::DB_TABLE, [
                [
                    DbSetup::COL_NAME     => 'class',
                    DbSetup::COL_TYPE     => DbSetup::COL_TYPE_VARCHAR,
                    DbSetup::COL_LENGTH   => 128,
                    DbSetup::COL_NULLABLE => false
                ]
            ]);
        }
    }

    /**
     * Collect executed setups from database
     */
    private function getExecutedSetups(): array
    {
        $this->checkSetupTable();
        return Connection::createInstance(self::DB_TABLE)->fetchCol();
    }

    public function prepareForUpgrade()
    {
        $this->cacheManager->flushCache(ModuleManager::CACHE_NAME);
        $this->moduleManager->initialize($this->app->getClassLoader());
    }

    /**
     * Collection setup processor name
     */
    public function collectSetups(): array
    {
        /** @var DirectoryScanner $scanner */
        $scanner = $this->diContainer->create(DirectoryScanner::class);
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            if (is_dir(($directory = $module[ModuleManager::MODULE_DIR] . '/Setup'))) {
                $scanner->addDirectory($directory);
            }
        }

        $setups = [];
        $executedSetups = $this->getExecutedSetups();
        foreach ($scanner->getClassNames() as $className) {
            if (in_array($className, $executedSetups)) {
                continue;
            }
            $reflectionClass = new ReflectionClass($className);
            if ($reflectionClass->isSubclassOf(AbstractSetup::class) && $reflectionClass->isInstantiable()) {
                $setups[] = $className;
            }
        }
        return $setups;
    }

    /**
     * Process a setup
     */
    public function process(string $processorClass)
    {
        $this->diContainer->create($processorClass)->execute();
        Connection::createInstance(self::DB_TABLE)->insert(['class' => $processorClass]);
    }
}
