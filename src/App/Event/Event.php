<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Event;

use Psr\EventDispatcher\StoppableEventInterface;

class Event implements StoppableEventInterface
{
    private string $name;
    private array $data = [];
    private bool $stopped = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns event name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get value with specified key
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Set value with specified key
     */
    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Stop processing all next listeners
     */
    public function preventPropagation(): self
    {
        $this->stopped = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }
}
