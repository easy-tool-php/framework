<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Module;

use Composer\Autoload\ClassLoader;
use DomainException;
use EasyTool\Framework\App\Cache\Manager as CacheManager;
use EasyTool\Framework\App\Di\Config as DiConfig;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Event\Config as EventConfig;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Exception\ModuleException;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\App\System\Config as SystemConfig;
use EasyTool\Framework\Code\Generator\ArrayGenerator;
use EasyTool\Framework\Filesystem\FileManager;
use EasyTool\Framework\Validation\Validator;
use Laminas\Code\Generator\FileGenerator;

class Manager
{
    public const CACHE_NAME = 'modules';

    public const ENABLED = 'enabled';
    public const DISABLED = 'disabled';

    public const DIR_CONFIG = 'config';

    public const MODULE_NAME = 'name';
    public const MODULE_NAMESPACE = 'namespace';
    public const MODULE_DIR = 'directory';
    public const MODULE_DEPENDS = 'depends';
    public const MODULE_ROUTE = 'route';

    private CacheManager $cacheManager;
    private Config $config;
    private DiConfig $diConfig;
    private DiContainer $diContainer;
    private Directory $directory;
    private EventConfig $eventConfig;
    private EventManager $eventManager;
    private FileManager $fileManager;
    private SystemConfig $systemConfig;
    private Validator $validator;

    private array $systemConfigData = [];
    private array $diData = [];
    private array $eventsData = [];
    private array $moduleStatus = [];
    private array $modules = [
        self::ENABLED  => [],
        self::DISABLED => []
    ];

    public function __construct(
        CacheManager $cacheManager,
        Config $config,
        DiConfig $diConfig,
        DiContainer $diContainer,
        Directory $directory,
        EventConfig $eventConfig,
        EventManager $eventManager,
        FileManager $fileManager,
        SystemConfig $systemConfig,
        Validator $validator
    ) {
        $this->cacheManager = $cacheManager;
        $this->config = $config;
        $this->diConfig = $diConfig;
        $this->diContainer = $diContainer;
        $this->directory = $directory;
        $this->eventConfig = $eventConfig;
        $this->eventManager = $eventManager;
        $this->fileManager = $fileManager;
        $this->systemConfig = $systemConfig;
        $this->validator = $validator;
    }

    /**
     * Try to collect modules and process related configuration
     * This method will be executed only on the cache is invalid.
     */
    private function initModules(ClassLoader $classLoader): void
    {
        /**
         * Developer is able to enable/disable a module through editing `app/config/modules.php`.
         *     Modules which do not exist in the config file will be added after initializing.
         */
        $this->diData = $this->eventsData = [];
        $this->modules = [self::ENABLED => [], self::DISABLED => []];
        $this->moduleStatus = is_file(($statusFile = $this->getStatusFile())) ? require $this->getStatusFile() : [];

        /**
         * Collect modules built by 3rd party from `vendor` folder
         */
        foreach ($classLoader->getPrefixesPsr4() as $namespace => $directoryGroup) {
            foreach ($directoryGroup as $directory) {
                try {
                    $this->collectModule(
                        $this->config->collectData($directory . '/' . self::DIR_CONFIG),
                        $namespace,
                        $directory
                    );
                } catch (DomainException $e) {
                    continue;
                }
            }
        }

        /**
         * Collect local customized modules from `app/modules` folder
         */
        $dir = $this->directory->getDirectoryPath(Directory::MODULES);
        foreach ($this->fileManager->getSubFolders($dir) as $moduleDir) {
            $directory = $dir . '/' . $moduleDir;
            if (($moduleConfig = $this->config->collectData($directory . '/' . self::DIR_CONFIG))) {
                $this->collectModule($moduleConfig, 'App\\' . $moduleDir . '\\', $directory);
            }
        }

        $this->checkDependency();
        usort($this->modules[self::ENABLED], [$this, 'sortModules']);
        $this->updateModuleStatus();

        foreach ($this->modules[self::ENABLED] as $moduleConfig) {
            $this->diData = array_merge(
                $this->diData,
                $this->diConfig->collectData($moduleConfig[self::MODULE_DIR] . '/' . self::DIR_CONFIG)
            );
            $this->eventsData = array_merge(
                $this->eventsData,
                $this->eventConfig->collectData($moduleConfig[self::MODULE_DIR] . '/' . self::DIR_CONFIG)
            );
            $this->systemConfigData = array_merge(
                $this->systemConfigData,
                $this->systemConfig->collectData($moduleConfig[self::MODULE_DIR] . '/' . self::DIR_CONFIG)
            );
        }
    }

    /**
     * Get status filepath
     */
    private function getStatusFile(): string
    {
        return $this->directory->getDirectoryPath(Directory::CONFIG) . '/modules.php';
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
     * Update modules status in `app/config/modules.php`
     */
    private function updateModuleStatus(): void
    {
        FileGenerator::fromArray(
            [
                'filename' => $this->getStatusFile(),
                'body'     => sprintf("return %s;\n", ArrayGenerator::fromArray($this->moduleStatus)->generate())
            ]
        )->write();
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
        if ($this->cacheManager->isEnabled(self::CACHE_NAME)) {
            $cache = $this->cacheManager->getCache(self::CACHE_NAME);
            if (($cachedModules = $cache->get())) {
                $this->modules = $cachedModules['modules'];
                $this->diData = $cachedModules['di'];
                $this->eventsData = $cachedModules['events'];
                $this->systemConfigData = $cachedModules['system_config'];
            } else {
                $this->initModules($classLoader);
                $cache->set(
                    [
                        'modules'       => $this->modules,
                        'di'            => $this->diData,
                        'events'        => $this->eventsData,
                        'system_config' => $this->systemConfigData
                    ]
                );
                $this->cacheManager->saveCache($cache);
            }
        } else {
            $this->initModules($classLoader);
        }

        $this->diContainer->appendConfig($this->diData);
        foreach ($this->eventsData as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $this->eventManager->addListener($eventName, $listener);
            }
        }
        $this->eventManager->dispatch(
            (new Event('after_modules_init'))->set('modules', $this->modules[self::ENABLED])
        );
        $this->systemConfig->addData($this->systemConfigData);
    }

    /**
     * Get all modules
     */
    public function getAllModules(): array
    {
        return array_merge($this->modules[self::ENABLED], $this->modules[self::DISABLED]);
    }

    /**
     * Get all enabled modules
     */
    public function getEnabledModules(): array
    {
        return $this->modules[self::ENABLED];
    }
}
