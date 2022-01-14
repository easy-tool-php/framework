<?php

namespace EasyTool\Framework\App\Setup;

use EasyTool\Framework\App;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\Database\Query;
use EasyTool\Framework\App\Database\Setup as DatabaseSetup;
use EasyTool\Framework\App\Database\Setup\Table;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\Module\Setup\AbstractSetup;
use EasyTool\Framework\App\ObjectManager;
use Laminas\Code\Scanner\DirectoryScanner;
use Laminas\Db\Metadata\Source\Factory;
use ReflectionClass;

class Upgrade
{
    public const DB_TABLE = 'executed_setups';

    private App $app;
    private CacheManager $cacheManager;
    private DatabaseManager $databaseManager;
    private DatabaseSetup $databaseSetup;
    private ModuleManager $moduleManager;
    private ObjectManager $objectManager;

    public function __construct(
        App $app,
        CacheManager $cacheManager,
        DatabaseManager $databaseManager,
        DatabaseSetup $databaseSetup,
        ModuleManager $moduleManager,
        ObjectManager $objectManager
    ) {
        $this->app = $app;
        $this->cacheManager = $cacheManager;
        $this->databaseManager = $databaseManager;
        $this->databaseSetup = $databaseSetup;
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
        $source = Factory::createSourceFromAdapter($adapter);
        if (!in_array(self::DB_TABLE, $source->getTableNames())) {
            $this->databaseSetup->createTable(self::DB_TABLE)
                ->addColumn([Table::COL_NAME => 'class', Table::COL_NULLABLE => false])
                ->process();
        }
    }

    /**
     * Collect executed setups from database
     */
    private function getExecutedSetups(): array
    {
        $this->checkSetupTable();

        /** @var Query $query */
        $query = $this->objectManager->create(Query::class, ['mainTable' => self::DB_TABLE]);
        return $query->fetchCol();
    }

    public function prepareForUpgrade()
    {
        $this->cacheManager->getCache(ModuleManager::CACHE_NAME)->clear();
        $this->moduleManager->initialize($this->app->getClassLoader());
    }

    /**
     * Collection setup processor name
     */
    public function collectSetupProcessors(): array
    {
        /** @var DirectoryScanner $scanner */
        $scanner = $this->objectManager->create(DirectoryScanner::class);
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
            if ($reflectionClass->isSubclassOf(AbstractSetup::class)) {
                $setups[] = $className;
            }
        }
        return $setups;
    }

    public function setup(string $processorClass)
    {
        $this->objectManager->create($processorClass)->upgrade();

        /** @var Query $query */
        $query = $this->objectManager->create(Query::class, ['mainTable' => self::DB_TABLE]);
        $query->insert(self::DB_TABLE, ['class' => $processorClass]);
    }
}
