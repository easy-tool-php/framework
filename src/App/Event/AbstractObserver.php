<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Data\DataObject;

abstract class AbstractObserver
{
    abstract public function execute(DataObject $data): void;
}
