<?php

namespace EasyTool\Framework\App\Setup;

use EasyTool\Framework\App;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\Module\Setup\AbstractSetup;
use EasyTool\Framework\App\ObjectManager;
use Laminas\Code\Scanner\DirectoryScanner;
use ReflectionClass;

class Upgrade
{
    public const DB_TABLE = 'executed_setups';

    private App $app;
    private CacheManager $cacheManager;
    private DatabaseManager $databaseManager;
    private ModuleManager $moduleManager;
    private ObjectManager $objectManager;

    public function __construct(
        App $app,
        CacheManager $cacheManager,
        DatabaseManager $databaseManager,
        ModuleManager $moduleManager,
        ObjectManager $objectManager
    ) {
        $this->app = $app;
        $this->cacheManager = $cacheManager;
        $this->databaseManager = $databaseManager;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Check whether the setup table is created
     *
     * Setup table is used to store names of the setup scripts which has been executed,
     *     so that to skip them at next upgrading.
     */
    private function checkSetupTable()
    {
        $adapter = $this->databaseManager->getAdapter();
    }

    /**
     * Collect executed setups from database
     */
    private function getExecutedSetups(): array
    {
        $this->checkSetupTable();

        return [];
    }

    public function process()
    {
        $this->cacheManager->getCache(ModuleManager::CACHE_NAME)->clear();
        $this->moduleManager->initialize($this->app->getClassLoader());

        /** @var DirectoryScanner $scanner */
        $scanner = $this->objectManager->create(DirectoryScanner::class);
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            if (is_dir(($directory = $module[ModuleManager::MODULE_DIR] . '/Setup'))) {
                $scanner->addDirectory($directory);
            }
        }
        $executedSetups = $this->getExecutedSetups();
        foreach ($scanner->getClassNames() as $className) {
            if (in_array($className, $executedSetups)) {
                continue;
            }
            $reflectionClass = new ReflectionClass($className);
            if ($reflectionClass->isSubclassOf(AbstractSetup::class)) {
                $this->objectManager->get($className)->upgrade();
            }
        }
    }
}
