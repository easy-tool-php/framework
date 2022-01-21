<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Config;

class Manager
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }
}
