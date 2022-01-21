<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Config\Source\File;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Http\Server\Router\Route\Api\Config\Collector as ConfigCollector;
use EasyTool\Framework\App\Module\Manager as ModuleManager;

class CollectApiRoutes implements ListenerInterface
{
    private ConfigCollector $configCollector;

    public function __construct(
        ConfigCollector $configCollector
    ) {
        $this->configCollector = $configCollector;
    }

    /**
     * Collect API route config from `[module]/config/api.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            $this->configCollector->addSource(
                File::createInstance()->setDirectory($moduleConfig[ModuleManager::MODULE_DIR])
            );
        }
        $this->configCollector->collect();
    }
}
