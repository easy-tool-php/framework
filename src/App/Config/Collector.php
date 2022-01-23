<?php

namespace EasyTool\Framework\App\Config;

use EasyTool\Framework\App\Filesystem\Directory;

class Collector
{
    private Directory $directory;
    private array $configPool;

    public function __construct(
        Directory $directory,
        array $configPool = []
    ) {
        $this->configPool = $configPool;
        $this->directory = $directory;
    }

    /**
     * Collect config data from `app/config` folder
     */
    public function collect(): void
    {
        $dir = $this->directory->getDirectoryPath(Directory::CONFIG);
        foreach ($this->configPool as $config) {
            $config->collectData($dir);
        }
    }
}
