<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Http\Server\Router\Route\Api as ApiRoute;
use EasyTool\Framework\App\Http\Server\Router\Route\Api\Config\Collector as ConfigCollector;
use EasyTool\Framework\App\Module\Manager as ModuleManager;

class CollectApiRoutes implements ListenerInterface
{
    private ApiRoute $apiRoute;
    private ConfigCollector $configCollector;

    public function __construct(
        ApiRoute $apiRoute,
        ConfigCollector $configCollector
    ) {
        $this->apiRoute = $apiRoute;
        $this->configCollector = $configCollector;
    }

    /**
     * Collect API route config from `[module]/config/api.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            echo $moduleConfig[ModuleManager::MODULE_DIR] . "\n";
            //$this->configCollector->addSource();
        }
        $this->configCollector->collect();
    }
}
