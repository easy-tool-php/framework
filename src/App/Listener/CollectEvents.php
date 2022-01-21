<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Module\Manager as ModuleManager;

class CollectEvents implements ListenerInterface
{
    private EventManager $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Collect event config from `[module]/config/events.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            echo $moduleConfig[ModuleManager::MODULE_DIR] . "\n";
        }
    }
}
