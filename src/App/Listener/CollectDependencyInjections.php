<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\App\ObjectManager\Config\Collector as ConfigCollector;

class CollectDependencyInjections implements ListenerInterface
{
    private ConfigCollector $configCollector;
    private ObjectManager $objectManager;

    public function __construct(
        ConfigCollector $configCollector,
        ObjectManager $objectManager
    ) {
        $this->configCollector = $configCollector;
        $this->objectManager = $objectManager;
    }

    /**
     * Collect dependency injection config from `[module]/config/di.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            echo $moduleConfig[ModuleManager::MODULE_DIR] . "\n";
            //$this->configCollector->addSource();
        }
        $this->configCollector->collect();
    }
}
