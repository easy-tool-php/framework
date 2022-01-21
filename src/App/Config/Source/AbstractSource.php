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
     * Update flag and do collecting
     */
    public function collect(): array
    {
        $this->collected = true;
        return $this->doCollect();
    }

    /**
     * Collect config data from the source
     */
    abstract protected function doCollect(): array;
}
