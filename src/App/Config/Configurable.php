<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\ObjectManager;

trait Configurable
{
    protected function initConfig(): array
    {
        /** @var Config $config */
        $config = ObjectManager::getInstance()->get(Config::class);
        return $config->collectConfigByName(self::CONFIG_NAME);
    }

    protected function updateConfig(array $config): void
    {
        /** @var Config $config */
        $config = ObjectManager::getInstance()->get(Config::class);
        $config->updateConfigByName(self::CONFIG_NAME, $config);
    }
}
