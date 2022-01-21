<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Event\Config\Collector as ConfigCollector;
use EasyTool\Framework\App\ObjectManager;
use Exception;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;

class Manager implements ListenerProviderInterface, EventDispatcherInterface
{
    public const CONFIG_NAME = 'events';

    private Config $config;
    private ConfigCollector $configCollector;
    private ObjectManager $objectManager;

    /**
     * listener array, format is like ['event_name' => [$listenerClassA, $listenerClassB, ...]]
     */
    private array $listeners = [];

    public function __construct(
        Config $config,
        ConfigCollector $configCollector,
        ObjectManager $objectManager
    ) {
        $this->config = $config;
        $this->configCollector = $configCollector;
        $this->objectManager = $objectManager;
    }

    /**
     * Collect listeners from `app/config/events.php`
     */
    public function initialize(): void
    {
        //$this->configCollector->addSource()->collect();
        $eventsConfig = $this->config->get('', self::CONFIG_NAME);
        foreach ($eventsConfig as $name => $listeners) {
            foreach ($listeners as $listener) {
                $this->addListener($name, $listener);
            }
        }
    }

    /**
     * Add event listener
     *
     * @param array $listener ['listener' => xxx, 'order' => xxx]
     */
    public function addListener(string $eventName, $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        $this->listeners[$eventName][] = $listener['listener'];
        usort($this->listeners[$eventName], function ($a, $b) {
            if (isset($a['order']) && !isset($b['order'])) {
                return 1;
            } elseif (!isset($a['order']) && isset($b['order'])) {
                return -1;
            } elseif (!isset($a['order']) && !isset($b['order'])) {
                return 0;
            } else {
                return $a['order'] > $b['order'] ? 1 : ($a['order'] < $b['order'] ? -1 : 0);
            }
        });
    }

    /**
     * Get all listeners for given event
     */
    public function getListenersForEvent(object $event): iterable
    {
        if (!($event instanceof Event)) {
            throw new InvalidArgumentException('Invalid event.');
        }
        return $this->listeners[$event->getName()];
    }

    /**
     * Dispatch event
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function dispatch(object $event): object
    {
        if (!($event instanceof Event)) {
            throw new InvalidArgumentException('Invalid event.');
        }
        if (!empty($this->listeners[$event->getName()])) {
            foreach (array_unique($this->listeners[$event->getName()]) as $listener) {
                $this->objectManager->create($listener)->process($event);
                if ($event->isPropagationStopped()) {
                    break;
                }
            }
        }
        return $event;
    }
}
