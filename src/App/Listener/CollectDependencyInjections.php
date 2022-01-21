<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Config\Source\File;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\App\ObjectManager\Config\Collector as ConfigCollector;

class CollectDependencyInjections implements ListenerInterface
{
    private Config $config;
    private ConfigCollector $configCollector;
    private ObjectManager $objectManager;

    public function __construct(
        Config $config,
        ConfigCollector $configCollector,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->configCollector = $configCollector;
        $this->objectManager = $objectManager;
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

        $this->objectManager->collectClassAliases(
            $this->config->get(null, $this->configCollector->getNamespace())
        );
    }
}
