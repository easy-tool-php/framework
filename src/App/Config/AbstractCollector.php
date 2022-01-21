<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Config\Source\AbstractSource;
use EasyTool\Framework\App\Exception\ConfigException;
use EasyTool\Framework\Validation\Validator;

abstract class AbstractCollector
{
    protected Config $config;
    protected Validator $validator;
    protected array $sources = [];
    protected string $namespace;

    public function __construct(
        Config $config,
        Validator $validator
    ) {
        $this->config = $config;
        $this->validator = $validator;
    }

    /**
     * Add config source
     */
    public function addSource(AbstractSource $source): self
    {
        $this->sources[] = $source;
        return $this;
    }

    /**
     * Collect config data from all sources
     */
    public function collect(): self
    {
        foreach ($this->sources as $source) {
            /** @var AbstractSource $source */
            if ($source->isCollected()) {
                continue;
            }
            if (!$this->validate(($config = $source->collect()))) {
                throw new ConfigException(sprintf('Invalid config `%s`.', $this->namespace));
            }
            $this->config->set(
                null,
                array_merge_recursive($this->config->get(null, $this->namespace), $config),
                $this->namespace
            );
        }
        return $this;
    }

    /**
     * To check whether the config data collected from source is valid
     */
    abstract public function validate(array $config): bool;
}
