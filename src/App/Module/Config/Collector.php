<?php

namespace EasyTool\Framework\App\Module\Config;

class Collector
{
    public function __construct()
    {
    }

    public function addConfig($config)
    {
        $this->configPool[] = $config;
    }
}
