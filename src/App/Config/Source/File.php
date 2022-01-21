<?php

namespace EasyTool\Framework\App\Config\Source;

class File extends AbstractSource
{
    private string $filename;

    /**
     * Set source filename
     */
    public function setFilename(string $filename): self
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function doCollect(): array
    {
        return (is_file($this->filename) && is_array(($config = require $this->filename))) ? $config : [];
    }
}
