<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\ObjectManager;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;

class Manager implements ListenerProviderInterface, EventDispatcherInterface
{
    public const CONFIG_NAME = 'events';

    private ConfigManager $configManager;
    private ObjectManager $objectManager;

    /**
     * listener array, format is like ['event_name' => [$listenerA, $listenerB, ...]]
     */
    private array $listeners = [];

    public function __construct(
        ConfigManager $configManager,
        ObjectManager $objectManager
    ) {
        $this->configManager = $configManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Collect config data from `app/config/events.php` and add events
     */
    public function initialize(): void
    {
        $listeners = $this->configManager->getConfig(self::CONFIG_NAME)->getData();
        foreach ($listeners as $name => $listener) {
            $this->addListener($name, $listener);
        }
    }

    /**
     * Add event listener
     *
     * @param array|string $listener Observer name
     */
    public function addListener(string $name, $listener): void
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = [];
        }
        if (!is_array($listener)) {
            $listener = [$listener];
        }
        $this->listeners[$name] = array_merge($this->listeners[$name], $listener);
    }

    /**
     * Get all events
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->listeners;
    }

    /**
     * Dispatch event
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function dispatch(object $event): object
    {
        $name = $event->get('name');
        if (!empty($this->listeners[$name])) {
            foreach (array_unique($this->listeners[$name]) as $listener) {
                $this->objectManager->create($listener)->process($event);
            }
        }
        return $event;
    }
}
