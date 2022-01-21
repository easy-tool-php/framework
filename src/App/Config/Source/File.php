<?php

namespace EasyTool\Framework\App\Config\Source;

/**
 * @method self createInstance()
 */
class File extends AbstractSource
{
    private string $directory;
    private ?string $file = null;

    /**
     * Get absolute filepath of the config file
     */
    private function getFilepath(): string
    {
        return $this->file
            ? ($this->directory . '/' . $this->file)
            : ($this->directory . '/' . $this->collector->getNamespace() . '.php');
    }

    /**
     * Set source directory
     */
    public function setDirectory(string $directory): self
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * Set source directory
     */
    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function doCollect(): array
    {
        $filename = $this->getFilepath();
        return (is_file($filename) && is_array(($config = require $filename))) ? $config : [];
    }
}
