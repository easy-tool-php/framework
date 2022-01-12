<?php

namespace EasyTool\Framework\App\Command;

use EasyTool\Framework\App;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Upgrade extends Command
{
    private App $app;
    private CacheManager $cacheManager;
    private ModuleManager $moduleManager;

    public function __construct(
        App $app,
        CacheManager $cacheManager,
        ModuleManager $moduleManager,
        string $name = null
    ) {
        $this->app = $app;
        $this->cacheManager = $cacheManager;
        $this->moduleManager = $moduleManager;
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

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cache = $this->cacheManager->getCache(ModuleManager::CACHE_NAME);
        $cache->clear();
        $this->moduleManager->initModules($this->app->getClassLoader());
        foreach ($this->moduleManager->getEnabledModules() as $module) {
        }

        return 0;
    }
}
