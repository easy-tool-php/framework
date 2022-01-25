<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Filesystem\Directory;
use Exception;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class Manager implements ListenerProviderInterface, EventDispatcherInterface
{
    private Config $config;
    private DiContainer $diContainer;
    private Directory $directory;

    /**
     * listener array, format is like ['event_name' => [$listenerClassA, $listenerClassB, ...]]
     */
    private array $listeners = [];

    public function __construct(
        Config $config,
        DiContainer $diContainer,
        Directory $directory
    ) {
        $this->config = $config;
        $this->diContainer = $diContainer;
        $this->directory = $directory;
    }

    /**
     * Collect listeners from `app/config/events.php`
     */
    public function initialize(): void
    {
        $eventsConfig = $this->config->collectData($this->directory->getDirectoryPath(Directory::CONFIG));
        foreach ($eventsConfig as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                $this->addListener($eventName, $listener);
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
     * @throws Exception
     */
    public function dispatch(object $event): object
    {
        if (!($event instanceof Event)) {
            throw new InvalidArgumentException('Invalid event.');
        }
        if (!empty($this->listeners[$event->getName()])) {
            foreach (array_unique($this->listeners[$event->getName()]) as $listener) {
                $this->diContainer->create($listener)->process($event);
                if ($event->isPropagationStopped()) {
                    break;
                }
            }
        }
        return $event;
    }
}
