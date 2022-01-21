<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;

class CollectDependencyInjections implements ListenerInterface
{
    /**
     * Collect dependency injection config from `[module]/config/di.php`
     */
    public function process(Event $event): void
    {
    }
}
