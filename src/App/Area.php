<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

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

    /**
     * Get current area code
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Set area code
     */
    public function setCode(string $code): self
    {
        if (!in_array($code, [self::FRONTEND, self::BACKEND, self::API, self::CLI])) {
            throw new InvalidArgumentException('Invalid area code.');
        }
        $this->code = $code;
        return $this;
    }
}
