<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\ObjectManager;
use Exception;
use ReflectionException;

class Manager
{
    public const CONFIG_NAME = 'events';

    private ConfigManager $configManager;
    private ObjectManager $objectManager;

    /**
     * Event array, format is like ['event_name' => [$observerA, $observerB, ...]]
     */
    private array $events = [];

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
        $events = $this->configManager->getConfig(self::CONFIG_NAME)->getData();
        foreach ($events as $name => $observer) {
            $this->addEvent($name, $observer);
        }
    }

    /**
     * Add event observer
     *
     * @param array|string $observer Observer name
     */
    public function addEvent(string $name, $observer): void
    {
        if (!isset($this->events[$name])) {
            $this->events[$name] = [];
        }
        if (!is_array($observer)) {
            $observer = [$observer];
        }
        $this->events[$name] = array_merge($this->events[$name], $observer);
    }

    /**
     * Get all events
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * Dispatch event
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function dispatch(string $name, array $data = []): void
    {
        if (!empty($this->events[$name])) {
            foreach (array_unique($this->events[$name]) as $observer) {
                $this->objectManager->create($observer)->execute(new DataObject($data));
            }
        }
    }
}