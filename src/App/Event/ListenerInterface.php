<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Event;

interface ListenerInterface
{
    /**
     * Process given event
     */
    public function process(Event $event): void;
}
