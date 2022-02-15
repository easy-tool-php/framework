<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Config\Source\File;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Http\Server\Router\Route\Api\Config as ApiConfig;
use EasyTool\Framework\App\Module\Manager as ModuleManager;

class CollectApiRoutes implements ListenerInterface
{
    private ApiConfig $apiConfig;

    public function __construct(ApiConfig $config)
    {
        $this->apiConfig = $config;
    }

    /**
     * Collect API route config from `[module]/config/api.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            $this->apiConfig->collectData(
                $moduleConfig[ModuleManager::MODULE_DIR] . '/' . ModuleManager::DIR_CONFIG
            );
        }
    }
}
