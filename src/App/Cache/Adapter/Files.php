<?php

namespace EasyTool\Framework\App\Cache\Adapter;

use EasyTool\Framework\App\Filesystem\Directory;

class Files implements AdapterInterface
{
    public const CODE = 'files';

    private Directory $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @inheritDoc
     */
    public function load(string $cacheName): array
    {
        $filename = $this->directory->getDirectoryPath(Directory::CACHE) . '/' . $cacheName;
        return is_file($filename) ? json_decode(file_get_contents($filename), true) : [];
    }

    /**
     * @inheritDoc
     */
    public function save(string $cacheName, array $data): void
    {
        if (!is_dir(($dir = $this->directory->getDirectoryPath(Directory::CACHE)))) {
            mkdir($dir, 0644, true);
        }
        file_put_contents($dir . '/' . $cacheName, json_encode($data));
    }

    /**
     * @inheritDoc
     */
    public function setConfig(array $config): self
    {
        return $this;
    }
}
