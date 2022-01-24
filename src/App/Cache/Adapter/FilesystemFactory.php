<?php

namespace EasyTool\Framework\App\Cache\Adapter;

use EasyTool\Framework\App\Filesystem\Directory;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Cache\Storage\Adapter\Filesystem;

class FilesystemFactory implements FactoryInterface
{
    private Directory $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @inheritDoc
     */
    public function create($options): AbstractAdapter
    {
        $options['cache_dir'] = $this->directory->getDirectoryPath(Directory::CACHE);
        return new Filesystem($options);
    }
}
