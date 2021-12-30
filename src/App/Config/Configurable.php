<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\ObjectManager;

trait Configurable
{
    public function initConfig(): array
    {
        /** @var Config $config */
        $config = ObjectManager::getInstance()->get(Config::class);
        return $config->collectConfigByName(self::CONFIG_NAME);
    }
}
