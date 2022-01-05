<?php

namespace EasyTool\Framework\App\Module;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Exception\ModuleException;
use EasyTool\Framework\App\FileManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Validation\Validator;

class Manager
{
    public const CACHE_NAME = 'modules';
    public const CACHE_MODULES = 'modules';

    public const CONFIG_NAME = 'modules';
    public const CONFIG_FILE = 'config/module.php';

    public const ENABLED = 'enabled';
    public const DISABLED = 'disabled';

    public const MODULE_NAME = 'name';
    public const MODULE_DIR = 'directory';
    public const MODULE_DEPENDS = 'depends';
    public const MODULE_ROUTE = 'route';

    private CacheManager $cacheManager;
    private ConfigManager $configManager;
    private EventManager $eventManager;
    private FileManager $fileManager;
    private ObjectManager $objectManager;
    private Validator $validator;

    private array $moduleStatus = [];

    private array $modules = [
        self::ENABLED => [],
        self::DISABLED => []
    ];

    public function __construct(
        CacheManager $cacheManager,
        ConfigManager $configManager,
        EventManager $eventManager,
        FileManager $fileManager,
        ObjectManager $objectManager,
        Validator $validator
    ) {
        $this->cacheManager = $cacheManager;
        $this->configManager = $configManager;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
        $this->objectManager = $objectManager;
        $this->validator = $validator;
    }

    /**
     * Collect config data from `app/config/modules.php` and initialize modules
     */
    public function initialize(ClassLoader $classLoader): void
    {
        $cache = $this->cacheManager->getCache(self::CACHE_NAME);
        if ($cache->isEnabled() && ($cachedModules = $cache->get(self::CACHE_MODULES))) {
            //$this->modules = $cachedModules;
            //return;
        }

        $config = $this->configManager->getConfig(self::CONFIG_NAME);
        $this->moduleStatus = $config->getData();

        /**
         * Assign the sub-folders under `app/modules` as PSR-4 directory,
         *     so that we can process them together with the ones got from composer.
         */
        $dir = $this->fileManager->getDirectoryPath(FileManager::DIR_MODULES);
        foreach ($this->fileManager->getSubFolders($dir) as $moduleDir) {
            $classLoader->addPsr4('App\\' . $moduleDir . '\\', $dir . '/' . $moduleDir);
        }

        foreach ($classLoader->getPrefixesPsr4() as $directoryGroup) {
            foreach ($directoryGroup as $directory) {
                if (($moduleConfig = $this->checkModuleConfig($directory))) {
                    $this->collectModule($moduleConfig, $directory);
                }
            }
        }

        $this->checkDependency();
        usort($this->modules[self::ENABLED], [$this, 'sortModules']);

        foreach ($this->modules[self::ENABLED] as $module) {
            $this->initModule($module);
        }

        $cache->set(self::CACHE_MODULES, $this->modules);
        $config->setData($this->moduleStatus)->save();
    }

    /**
     * As some modules are depend on others,
     *     to check whether all depended modules are enabled is necessary
     */
    private function checkDependency()
    {
        foreach ($this->modules[self::ENABLED] as $module) {
            if (empty($module[self::MODULE_DEPENDS])) {
                continue;
            }
            foreach ($module[self::MODULE_DEPENDS] as $depend) {
                if (!isset($this->modules[$depend])) {
                    throw new ModuleException(
                        sprintf(
                            'Module dependency `%s` of module `%s` does not exist.',
                            $depend,
                            $module[self::MODULE_NAME]
                        )
                    );
                }
            }
        }
    }

    /**
     * Sort modules based on the `depends` attribute
     */
    private function sortModules($a, $b)
    {
        if (
            !empty($b[self::MODULE_DEPENDS])
            && in_array($a[self::MODULE_NAME], $b[self::MODULE_DEPENDS])
        ) {
            return 1;
        }
        if (
            !empty($a[self::MODULE_DEPENDS])
            && in_array($b[self::MODULE_NAME], $a[self::MODULE_DEPENDS])
        ) {
            return -1;
        }
        return 0;
    }

    /**
     * Check whether given folder is a module directory
     */
    private function checkModuleConfig(string $directory): ?array
    {
        return (is_file(($configFile = $directory . '/' . self::CONFIG_FILE))
            && is_array(($config = require $configFile))
            && $this->validator->validate(
                [
                    self::MODULE_NAME => ['required'],
                    self::MODULE_DEPENDS => ['array'],
                    self::MODULE_ROUTE => ['array', 'options' => [Area::FRONTEND, Area::BACKEND, Area::API]]
                ],
                $config
            ))
            ? $config : null;
    }

    /**
     * Collect module
     */
    private function collectModule(array $config, $directory): void
    {
        $config[self::MODULE_DIR] = $directory;
        if (!isset($this->moduleStatus[$config[self::MODULE_NAME]])) {
            $this->moduleStatus[$config[self::MODULE_NAME]] = true;
        }
        if ($this->moduleStatus[$config[self::MODULE_NAME]]) {
            $this->modules[self::ENABLED][$config[self::MODULE_NAME]] = $config;
        } else {
            $this->modules[self::DISABLED][$config[self::MODULE_NAME]] = $config;
        }
    }

    /**
     * Initialize each module
     */
    private function initModule(array $config): void
    {
        //$this->eventManager->addEvent();
        //$this->objectManager->collectClassAliases();
    }
}
