<?php

namespace EasyTool\Framework\App\Event;

interface ListenerInterface
{
    /**
     * Process given event
     */
    public function process(Event $event): void;
}
