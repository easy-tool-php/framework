<?php

namespace EasyTool\Framework\App\Config\Source;

use EasyTool\Framework\App\Config\AbstractCollector;
use EasyTool\Framework\App\ObjectManager;

abstract class AbstractSource
{
    protected AbstractCollector $collector;
    protected bool $collected = false;

    /**
     * Check whether the source is collected
     */
    public function isCollected(): bool
    {
        return $this->collected;
    }

    /**
     * Update flag and do collecting
     */
    public function collect(): array
    {
        $this->collected = true;
        return $this->doCollect();
    }

    /**
     * Set collector
     */
    public function setCollector(AbstractCollector $collector): self
    {
        $this->collector = $collector;
        return $this;
    }

    /**
     * A quick way to get new source instance
     */
    public static function createInstance(): self
    {
        return ObjectManager::getInstance()->create(static::class);
    }

    /**
     * Collect config data from the source
     */
    abstract protected function doCollect(): array;
}
