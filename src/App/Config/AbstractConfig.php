<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Config\Source\AbstractSource;
use EasyTool\Framework\App\Exception\ConfigException;

abstract class AbstractConfig
{
    protected string $name;
    protected array $sources;
    protected array $data = [];

    public function addSource($source)
    {
        $this->sources[] = $source;
    }

    public function collect(): self
    {
        foreach ($this->sources as $source) {
            /** @var AbstractSource $source */
            if ($source->isCollected()) {
                continue;
            }
            if (!$this->validate(($config = $source->collect()))) {
                throw new ConfigException(sprintf('Invalid config `%s`.', $this->name));
            }
            $this->data = array_merge_recursive($this->data, $config);
        }
        return $this;
    }

    abstract public function validate(array $config): bool;
}
