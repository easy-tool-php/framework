<?php

namespace EasyTool\Framework\App\Config\Source;

abstract class AbstractSource
{
    protected bool $collected = false;

    /**
     * Check whether the source is collected
     */
    public function isCollected(): bool
    {
        return $this->collected;
    }

    /**
     * Collect config data from the source
     */
    abstract public function collect(): array;
}
