<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config\AbstractConfig;

class Config extends AbstractConfig
{
    public const NAME = 'events';

    public function collectData()
    {
        echo get_class($this);
    }
}
