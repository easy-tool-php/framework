<?php

namespace EasyTool\Framework\App\Config;

class Manager
{
    private array $appConfigCollectors;

    public function __construct(array $appConfigCollectors = [])
    {
        $this->appConfigCollectors = $appConfigCollectors;
    }

    public function collectAppConfig()
    {
        foreach ($this->appConfigCollectors as $configCollector) {
            $configCollector->collect();
        }
    }
}
