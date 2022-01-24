<?php

namespace EasyTool\Framework\App\Config;

use DomainException;
use EasyTool\Framework\Validation\Validator;

abstract class AbstractFileConfig extends AbstractConfig
{
    protected Validator $validator;
    protected string $filename;
    protected array $format;

    public function __construct(Validator $validator, array $data = [])
    {
        $this->validator = $validator;
        parent::__construct($data);
    }

    /**
     * Collect data from specified directory
     */
    public function collectData($dir): self
    {
        $configData = require $dir . '/' . $this->filename;
        if (!$this->validator->validate($this->format, $configData)) {
            throw new DomainException('Invalid config.');
        }
        $this->setData($configData);
        return $this;
    }
}
