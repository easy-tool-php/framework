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
    public function collectData($dir): array
    {
        $configData = is_file(($filepath = $dir . '/' . $this->filename)) ? (require $filepath) : [];
        if (!$this->validator->validate($this->format, $configData)) {
            throw new DomainException(sprintf('Invalid config data in file `%s`.', $filepath));
        }
        return $configData;
    }

    /**
     * Check whether given config data is valid
     */
    public function validate(array $configData): bool
    {
        return $this->validator->validate($this->format, $configData);
    }
}
