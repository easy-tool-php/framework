<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Config\Source\File;
use EasyTool\Framework\App\Event\Config as EventConfig;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;

class CollectEvents implements ListenerInterface
{
    private EventConfig $eventConfig;
    private EventManager $eventManager;

    public function __construct(
        EventConfig $eventConfig,
        EventManager $eventManager
    ) {
        $this->eventConfig = $eventConfig;
        $this->eventManager = $eventManager;
    }

    /**
     * Collect event config from `[module]/config/events.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            $eventsConfig = $this->eventConfig->collectData(
                $moduleConfig[ModuleManager::MODULE_DIR] . '/' . ModuleManager::DIR_CONFIG
            );
            foreach ($eventsConfig as $eventName => $listeners) {
                foreach ($listeners as $listener) {
                    $this->eventManager->addListener($eventName, $listener);
                }
            }
        }
    }
}
