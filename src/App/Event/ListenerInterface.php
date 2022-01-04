<?php

namespace EasyTool\Framework\App\Event;

interface ListenerInterface
{
    public function process(object $event): void;
}
