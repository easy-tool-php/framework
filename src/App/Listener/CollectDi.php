<?php

namespace EasyTool\Framework\App\Listener;

use EasyTool\Framework\App\Config\Source\File;
use EasyTool\Framework\App\Di\Config as DiConfig;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Event\Event;
use EasyTool\Framework\App\Event\ListenerInterface;
use EasyTool\Framework\App\Module\Manager as ModuleManager;

class CollectDi implements ListenerInterface
{
    private DiConfig $diConfig;
    private DiContainer $diContainer;

    public function __construct(
        DiConfig $diConfig,
        DiContainer $diContainer
    ) {
        $this->diConfig = $diConfig;
        $this->diContainer = $diContainer;
    }

    /**
     * Collect dependency injection config from `[module]/config/di.php`
     */
    public function process(Event $event): void
    {
        foreach ($event->get('modules') as $moduleConfig) {
            $this->diContainer->appendConfig(
                $this->diConfig->collectData(
                    $moduleConfig[ModuleManager::MODULE_DIR] . '/' . ModuleManager::DIR_CONFIG
                )
            );
        }
    }
}
