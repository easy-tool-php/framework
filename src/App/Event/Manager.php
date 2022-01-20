<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Validation\Validator;
use Exception;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;

class Manager implements ListenerProviderInterface, EventDispatcherInterface
{
    private Config $config;
    private ObjectManager $objectManager;
    private Validator $validator;

    /**
     * listener array, format is like ['event_name' => [$listenerClassA, $listenerClassB, ...]]
     */
    private array $listeners = [];

    public function __construct(
        Config $config,
        ObjectManager $objectManager,
        Validator $validator
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->validator = $validator;
    }

    /**
     * Check whether a listener config is valid
     */
    public function validateConfig($config)
    {
        return $this->validator->validate(
            [
                '*.*.listener' => ['required', 'string'],
                '*.*.order'    => ['int']
            ],
            $config
        );
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
        $this->listeners[$eventName][] = $listener;
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
