<?php

namespace EasyTool\Framework\App\Cache\Adapter\Filesystem;

use EasyTool\Framework\App\Cache\Adapter\FactoryInterface;
use EasyTool\Framework\App\Cache\Adapter\Filesystem;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Filesystem\Directory;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;

class Factory implements FactoryInterface
{
    private DiContainer $diContainer;
    private Directory $directory;

    public function __construct(
        DiContainer $diContainer,
        Directory $directory
    ) {
        $this->diContainer = $diContainer;
        $this->directory = $directory;
    }

    /**
     * @inheritDoc
     */
    public function create($options): AbstractAdapter
    {
        $options['cache_dir'] = $this->directory->getDirectoryPath(Directory::CACHE);
        return $this->diContainer->create(Filesystem::class, ['options' => $options]);
    }
}
