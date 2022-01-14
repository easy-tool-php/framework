<?php

namespace EasyTool\Framework\App\Command;

use EasyTool\Framework\App;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Database\Manager as DatabaseManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use Laminas\Code\Scanner\DirectoryScanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Upgrade extends Command
{
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
        ObjectManager $objectManager,
        string $name = null
    ) {
        $this->app = $app;
        $this->cacheManager = $cacheManager;
        $this->databaseManager = $databaseManager;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('setup:upgrade')
            ->setDescription('Check all enabled modules for database upgrade');
    }

    private function checkSetupTable()
    {
        $adapter = $this->databaseManager->getAdapter();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cacheManager->getCache(ModuleManager::CACHE_NAME)->clear();
        $this->moduleManager->initialize($this->app->getClassLoader());

        $this->checkSetupTable();

        /** @var DirectoryScanner $scanner */
        $scanner = $this->objectManager->create(DirectoryScanner::class);
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            if (is_dir(($directory = $module[ModuleManager::MODULE_DIR] . '/Setup'))) {
                $scanner->addDirectory($directory);
            }
        }
        foreach ($scanner->getClassNames() as $className) {
            $reflectionClass = new \ReflectionClass($className);
            if ($reflectionClass->isSubclassOf(\EasyTool\Framework\App\Module\Setup\AbstractSetup::class)) {
                $this->objectManager->get($className)->upgrade();
            }
        }
        return 0;
    }
}
