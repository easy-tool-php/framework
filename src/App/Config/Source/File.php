<?php

namespace EasyTool\Framework\App\Config\Source;

class File extends AbstractSource
{
    /**
     * @inheritDoc
     */
    public function collect(): array
    {
        return (is_file($this->file) && is_array(($config = require $this->file))) ? $config : [];
    }
}
