<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Config\Source\File;
use EasyTool\Framework\App\Event\Config\Collector as ConfigCollector;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;

class CollectEvents implements ListenerInterface
{
    private Config $config;
    private ConfigCollector $configCollector;
    private EventManager $eventManager;

    public function __construct(
        Config $config,
        ConfigCollector $configCollector,
        EventManager $eventManager
    ) {
        $this->config = $config;
        $this->configCollector = $configCollector;
        $this->eventManager = $eventManager;
    }

    /**
     * Collect event config from `[module]/config/events.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            $this->configCollector->addSource(
                File::createInstance()->setDirectory($moduleConfig[ModuleManager::MODULE_DIR])
            );
        }
        $this->configCollector->collect();

        $eventsConfig = $this->config->get('', $this->configCollector->getNamespace());
        foreach ($eventsConfig as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $this->eventManager->addListener($eventName, $listener);
            }
        }
    }
}
