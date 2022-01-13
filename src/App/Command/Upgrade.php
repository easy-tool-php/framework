<?php

namespace EasyTool\Framework\App\Command;

use EasyTool\Framework\App;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\Filesystem\FileManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Upgrade extends Command
{
    private App $app;
    private CacheManager $cacheManager;
    private FileManager $fileManager;
    private ModuleManager $moduleManager;

    public function __construct(
        App $app,
        CacheManager $cacheManager,
        FileManager $fileManager,
        ModuleManager $moduleManager,
        string $name = null
    ) {
        $this->app = $app;
        $this->cacheManager = $cacheManager;
        $this->fileManager = $fileManager;
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
        $this->cacheManager->getCache(ModuleManager::CACHE_NAME)->clear();
        $this->moduleManager->initModules($this->app->getClassLoader());
        foreach ($this->moduleManager->getEnabledModules() as $module) {
            $files = $this->fileManager->getFiles($module[ModuleManager::MODULE_DIR] . '/Setup', true, true);
            foreach ($files as $file) {
                if (($pos = strrpos($file, '.')) && strtolower(substr($file, $pos)) == '.php') {
                    $class = '\\' . $module[ModuleManager::MODULE_NAMESPACE] . 'Setup\\'
                        . str_replace('/', '\\', substr($file, 0, $pos));
                    $reflectionClass = new ReflectionClass($class);
                }
            }
        }
        return 0;
    }
}
