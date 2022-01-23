<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Config\Source\File;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Di\Container\Config\Collector as ConfigCollector;

class CollectDependencyInjections implements ListenerInterface
{
    private Config $config;
    private ConfigCollector $configCollector;
    private DiContainer $diContainer;

    public function __construct(
        Config $config,
        ConfigCollector $configCollector,
        DiContainer $diContainer
    ) {
        $this->config = $config;
        $this->configCollector = $configCollector;
        $this->diContainer = $diContainer;
    }

    /**
     * Collect dependency injection config from `[module]/config/di.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            $this->configCollector->addSource(
                File::createInstance()->setDirectory($moduleConfig[ModuleManager::MODULE_DIR])
            );
        }
        $this->configCollector->collect();

        $this->diContainer->collectClassAliases(
            $this->config->get(null, $this->configCollector->getNamespace())
        );
    }
}
