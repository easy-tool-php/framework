<?php

namespace EasyTool\Framework\App\Module;

use Composer\Autoload\ClassLoader;
use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Exception\ModuleException;
use EasyTool\Framework\App\FileManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Validation\Validator;

class Manager
{
    public const CACHE_NAME = 'modules';
    public const CACHE_MODULES = 'modules';
    public const CACHE_API = 'api';
    public const CACHE_DI = 'di';
    public const CACHE_EVENTS = 'events';

    public const CONFIG_NAME = 'modules';
    public const CONFIG_FILE = 'config/module.php';
    public const CONFIG_TYPE_API = 'api';
    public const CONFIG_TYPE_DI = 'di';
    public const CONFIG_TYPE_EVENTS = 'events';

    public const ENABLED = 'enabled';
    public const DISABLED = 'disabled';

    public const MODULE_NAME = 'name';
    public const MODULE_NAMESPACE = 'namespace';
    public const MODULE_DIR = 'directory';
    public const MODULE_DEPENDS = 'depends';
    public const MODULE_ROUTE = 'route';
    public const MODULE_DI = 'di';
    public const MODULE_EVENTS = 'events';

    private CacheManager $cacheManager;
    private Config $config;
    private EventManager $eventManager;
    private FileManager $fileManager;
    private ObjectManager $objectManager;
    private Validator $validator;

    private array $moduleStatus = [];
    private array $apiRoutes = [];
    private array $eventListeners = [];
    private array $classAliases = [];

    private array $modules = [
        self::ENABLED => [],
        self::DISABLED => []
    ];

    public function __construct(
        CacheManager $cacheManager,
        Config $config,
        EventManager $eventManager,
        FileManager $fileManager,
        ObjectManager $objectManager,
        Validator $validator
    ) {
        $this->cacheManager = $cacheManager;
        $this->config = $config;
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
        /**
         * Assign the sub-folders under `app/modules` as PSR-4 directory,
         *     so that system is able to autoload the local modules.
         */
        $dir = $this->fileManager->getDirectoryPath(FileManager::DIR_MODULES);
        foreach ($this->fileManager->getSubFolders($dir) as $moduleDir) {
            $classLoader->addPsr4('App\\' . $moduleDir . '\\', $dir . '/' . $moduleDir);
        }

        /**
         * Collect all necessary data from cache in order to save memory and improve performance.
         *     Skip this step if the cache is disabled or empty.
         */
        $cache = $this->cacheManager->getCache(self::CACHE_NAME);
        if ($cache->isEnabled() && ($cachedModules = $cache->get(self::CACHE_MODULES))) {
            $this->modules = $cachedModules;
            $this->apiRoutes = $cache->get(self::CACHE_API);
            $this->classAliases = $cache->get(self::CACHE_DI);
            $this->eventListeners = $cache->get(self::CACHE_EVENTS);
            $this->prepareForApp();
            return;
        }

        /**
         * Developer is able to enable/disable a module through editing `app/config/modules.php`.
         *     Modules which do not exist in the config file will be added after initializing.
         */
        $this->moduleStatus = $this->config->get(null, self::CONFIG_NAME);
        foreach ($classLoader->getPrefixesPsr4() as $namespace => $directoryGroup) {
            foreach ($directoryGroup as $directory) {
                if (($moduleConfig = $this->checkModuleConfig($directory))) {
                    $this->collectModule($moduleConfig, $namespace, $directory);
                }
            }
        }
        $this->checkDependency();
        usort($this->modules[self::ENABLED], [$this, 'sortModules']);
        foreach ($this->modules[self::ENABLED] as $module) {
            $this->initModule($module);
        }
        $this->prepareForApp();

        $cache->set(self::CACHE_MODULES, $this->modules);
        $cache->set(self::CACHE_API, $this->apiRoutes);
        $cache->set(self::CACHE_DI, $this->classAliases);
        $cache->set(self::CACHE_EVENTS, $this->eventListeners);

        $this->config->set(null, $this->moduleStatus, self::CONFIG_NAME)->save(self::CONFIG_NAME);
    }

    /**
     * Prepare for running the application after collecting module config
     */
    private function prepareForApp()
    {
        foreach ($this->eventListeners as $name => $listener) {
            $this->eventManager->addListener($name, $listener);
        }
        $this->objectManager->collectClassAliases($this->classAliases);
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
                    self::MODULE_ROUTE => ['array', 'options' => [Area::FRONTEND, Area::BACKEND, Area::API]],
                    self::MODULE_DI => ['array'],
                    self::MODULE_EVENTS => ['array']
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
     * Get config data of specified type
     */
    private function getConfig(array $moduleConfig, string $type): array
    {
        if (
            is_file(($configFile = $moduleConfig[self::MODULE_DIR] . '/config/' . $type . '.php'))
            && is_array(($config = require $configFile))
        ) {
            return $config;
        }
        return [];
    }

    /**
     * Initialize each module
     */
    private function initModule(array $moduleConfig): void
    {
        $this->apiRoutes = array_merge(
            $this->apiRoutes,
            $this->getConfig($moduleConfig, self::CONFIG_TYPE_API)
        );
        $this->classAliases = array_merge(
            $this->classAliases,
            $this->getConfig($moduleConfig, self::CONFIG_TYPE_DI)
        );
        $this->eventListeners = array_merge(
            $this->eventListeners,
            $this->getConfig($moduleConfig, self::CONFIG_TYPE_EVENTS)
        );
    }

    /**
     * Get all API routes
     */
    public function getApiRoutes(): array
    {
        return $this->apiRoutes;
    }

    /**
     * Get all enabled modules
     */
    public function getEnabledModules(): array
    {
        return $this->modules[self::ENABLED];
    }
}
