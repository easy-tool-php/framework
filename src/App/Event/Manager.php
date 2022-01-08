<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\ObjectManager;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;

class Manager implements ListenerProviderInterface, EventDispatcherInterface
{
    public const CONFIG_NAME = 'events';

    private Config $config;
    private ObjectManager $objectManager;

    /**
     * listener array, format is like ['event_name' => [$listenerClassA, $listenerClassB, ...]]
     */
    private array $listeners = [];

    public function __construct(
        Config $config,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Collect config data from `app/config/events.php` and add events
     */
    public function initialize(): void
    {
        $listeners = $this->config->get(null, self::CONFIG_NAME);
        foreach ($listeners as $name => $listener) {
            $this->addListener($name, $listener);
        }
    }

    /**
     * Add event listener
     *
     * @param array|string $listener Listener name
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
     * Get all listeners for given event
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
