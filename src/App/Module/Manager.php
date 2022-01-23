<?php

namespace EasyTool\Framework\App\Module;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App;
use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Exception\ModuleException;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\Filesystem\FileManager;
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
    public const MODULE_NAMESPACE = 'namespace';
    public const MODULE_DIR = 'directory';
    public const MODULE_DEPENDS = 'depends';
    public const MODULE_ROUTE = 'route';

    private CacheManager $cacheManager;
    private Config $config;
    private Directory $directory;
    private EventManager $eventManager;
    private FileManager $fileManager;
    private Validator $validator;

    private array $moduleStatus = [];

    private array $modules = [
        self::ENABLED  => [],
        self::DISABLED => []
    ];

    public function __construct(
        Directory $directory,
        CacheManager $cacheManager,
        Config $config,
        EventManager $eventManager,
        FileManager $fileManager,
        Validator $validator
    ) {
        $this->directory = $directory;
        $this->cacheManager = $cacheManager;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
        $this->validator = $validator;
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
                    self::MODULE_NAME    => ['required'],
                    self::MODULE_DEPENDS => ['array'],
                    self::MODULE_ROUTE   => ['array', 'options' => [Area::FRONTEND, Area::BACKEND, Area::API]]
                ],
                $config
            ))
            ? $config : null;
    }

    /**
     * Collect module
     */
    private function collectModule(array $config, $namespace, $directory): void
    {
        $config[self::MODULE_DIR] = $directory;
        $config[self::MODULE_NAMESPACE] = $namespace;
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
     * Initialize each module
     */
    private function initModule(array $moduleConfig): void
    {
    }

    /**
     * Collect config data from `app/config/modules.php` and initialize modules.
     * This method may be executed on upgrading.
     */
    public function initialize(ClassLoader $classLoader): void
    {
        /**
         * Collect all necessary data from cache in order to save memory and improve performance.
         *     Skip this step if the cache is disabled or empty.
         */
        $cache = $this->cacheManager->getCache(self::CACHE_NAME);
        if ($cache->isEnabled() && ($cachedModules = $cache->get(self::CACHE_MODULES))) {
            $this->modules = $cachedModules;
        } else {
            /**
             * Developer is able to enable/disable a module through editing `app/config/modules.php`.
             *     Modules which do not exist in the config file will be added after initializing.
             */
            $this->modules = [self::ENABLED => [], self::DISABLED => []];
            $this->moduleStatus = $this->config->getData();

            /**
             * Collect modules built by 3rd party from `vendor` folder
             */
            foreach ($classLoader->getPrefixesPsr4() as $namespace => $directoryGroup) {
                foreach ($directoryGroup as $directory) {
                    if (($moduleConfig = $this->checkModuleConfig($directory))) {
                        $this->collectModule($moduleConfig, $namespace, $directory);
                    }
                }
            }

            /**
             * Collect local customized modules from `app/modules` folder
             */
            $dir = $this->directory->getDirectoryPath(App::DIR_MODULES);
            foreach ($this->fileManager->getSubFolders($dir) as $moduleDir) {
                $directory = $dir . '/' . $moduleDir;
                if (($moduleConfig = $this->checkModuleConfig($directory))) {
                    $this->collectModule($moduleConfig, 'App\\' . $moduleDir . '\\', $directory);
                }
            }

            $this->checkDependency();
            usort($this->modules[self::ENABLED], [$this, 'sortModules']);
            foreach ($this->modules[self::ENABLED] as $module) {
                $this->initModule($module);
            }
            $this->config->set(null, $this->moduleStatus, self::CONFIG_NAME);

            $cache->set(self::CACHE_MODULES, $this->modules);
            $cache->save();
        }

        $this->eventManager->dispatch((new Event('after_modules_init'))->set('modules', $this->modules[self::ENABLED]));
    }

    /**
     * Get all enabled modules
     */
    public function getEnabledModules(): array
    {
        return $this->modules[self::ENABLED];
    }

    /**
     * Get all modules
     */
    public function getAllModules(): array
    {
        return array_merge($this->modules[self::ENABLED], $this->modules[self::DISABLED]);
    }
}
