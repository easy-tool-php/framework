<?php

namespace EasyTool\Framework\App\Module;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\FileManager;

class Manager
{
    public const APP_MODULES = 'modules';
    public const CONFIG_NAME = 'modules';
    public const CONFIG_FILE = 'config/module.php';

    public const MODULE_NAME = 'name';
    public const MODULE_DEPENDS = 'depends';
    public const MODULE_ROUTE = 'route';

    private CacheManager $cacheManager;
    private ConfigManager $configManager;
    private EventManager $eventManager;
    private FileManager $fileManager;

    private array $modules = [];
    private array $moduleStatus = [];

    public function __construct(
        CacheManager $cacheManager,
        ConfigManager $configManager,
        EventManager $eventManager,
        FileManager $fileManager
    ) {
        $this->cacheManager = $cacheManager;
        $this->configManager = $configManager;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
    }

    /**
     * Collect config data from `app/config/modules.php` and initialize modules
     */
    public function initialize(ClassLoader $classLoader): void
    {
        $config = $this->configManager->getConfig(self::CONFIG_NAME);
        $this->moduleStatus = $config->getData();

        /**
         * Assign the sub-folders under `app/modules` as PSR-4 directory,
         *     so that we can process them together with the ones got from composer.
         */
        $dir = $this->fileManager->getDirectoryPath(FileManager::DIR_APP) . '/' . self::APP_MODULES;
        foreach ($this->fileManager->getSubFolders($dir) as $moduleDir) {
            $classLoader->addPsr4('App\\' . $moduleDir . '\\', $dir . '/' . $moduleDir);
        }

        foreach ($classLoader->getPrefixesPsr4() as $directoryGroup) {
            foreach ($directoryGroup as $directory) {
                if (($moduleConfig = $this->checkModuleConfig($directory))) {
                    $this->initModule($moduleConfig, $directory);
                }
            }
        }
        $config->setData($this->moduleStatus)->save();
    }

    /**
     * Check whether given folder is a module directory
     */
    private function checkModuleConfig(string $directory): ?array
    {
        return (is_file(($configFile = $directory . '/' . self::CONFIG_FILE))
            && is_array(($config = require $configFile))
            && !empty($config[self::MODULE_NAME]))
            ? $config : null;
    }

    private function initModule(array $config, $directory)
    {
        if (isset($this->moduleStatus[$config[self::MODULE_NAME]])) {
            if (!$this->moduleStatus[$config[self::MODULE_NAME]]) {
                return;
            }
        } else {
            $this->moduleStatus[$config[self::MODULE_NAME]] = true;
        }
    }
}
