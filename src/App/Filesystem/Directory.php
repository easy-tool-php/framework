<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Filesystem;

use EasyTool\Framework\App\Exception\FileException;
use InvalidArgumentException;

class Directory
{
    public const APP = 'app';
    public const CACHE = 'cache';
    public const CONFIG = 'config';
    public const LOG = 'log';
    public const MODULES = 'app/modules';
    public const PUB = 'pub';
    public const ROOT = 'root';
    public const TMP = 'tmp';
    public const VAR = 'var';

    private ?string $root = null;

    /**
     * Set application directory root
     */
    public function setRoot($root): void
    {
        $this->root = $root;
    }

    /**
     * Get absolute path of directory with specified type,
     *     the types are defined as static variables of this class.
     *
     * @throws FileException
     */
    public function getDirectoryPath(string $type): string
    {
        switch ($type) {
            case self::ROOT:
                return $this->root . '/';

            case self::APP:
                return $this->root . '/app';

            case self::CONFIG:
                return $this->root . '/app/config';

            case self::MODULES:
                return $this->root . '/app/modules';

            case self::PUB:
                return $this->root . '/pub';

            case self::VAR:
                return $this->root . '/var';

            case self::CACHE:
                return $this->root . '/var/cache';

            case self::LOG:
                return $this->root . '/var/log';

            case self::TMP:
                return $this->root . '/var/tmp';

            default:
                throw new InvalidArgumentException('Directory type is not supported.');
        }
    }
}
