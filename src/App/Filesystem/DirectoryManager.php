<?php

namespace EasyTool\Framework\App\Filesystem;

class DirectoryManager
{
    public const APP = 'app';
    public const CONFIG = 'config';
    public const LOG = 'log';
    public const PUB = 'pub';
    public const TMP = 'tmp';
    public const VAR = 'var';

    private ?string $directoryRoot = null;

    public function initialize(string $directoryRoot): void
    {
        $this->directoryRoot = $directoryRoot;
    }

    public function getAbsolutePath($type)
    {
        switch ($type) {
            case self::APP:
                return $this->directoryRoot . '/app';

            case self::CONFIG:
                return $this->directoryRoot . '/app/config';

            case self::LOG:
                return $this->directoryRoot . '/var/log';

            case self::PUB:
                return $this->directoryRoot . '/pub';

            case self::TMP:
                return $this->directoryRoot . '/var/tmp';

            case self::VAR:
                return $this->directoryRoot . '/var';
        }
    }
}
