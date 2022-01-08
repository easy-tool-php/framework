<?php

namespace EasyTool\Framework\App;

use InvalidArgumentException;

class Area
{
    public const GLOBAL = 'global';
    public const FRONTEND = 'frontend';
    public const BACKEND = 'backend';
    public const API = 'api';
    public const CLI = 'cli';

    private string $code = self::GLOBAL;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        if (!in_array($code, [self::FRONTEND, self::BACKEND, self::API, self::CLI])) {
            throw new InvalidArgumentException('Invalid area code.');
        }
        $this->code = $code;
        return $this;
    }
}
