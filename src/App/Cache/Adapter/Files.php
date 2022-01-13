<?php

namespace EasyTool\Framework\App\Cache\Adapter;

use EasyTool\Framework\App;

class Files implements AdapterInterface
{
    public const CODE = 'files';

    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @inheritDoc
     */
    public function load(string $cacheName): array
    {
        $filename = $this->app->getDirectoryPath(App::DIR_CACHE) . '/' . $cacheName;
        return is_file($filename) ? json_decode(file_get_contents($filename), true) : [];
    }

    /**
     * @inheritDoc
     */
    public function save(string $cacheName, array $data): void
    {
        if (!is_dir(($dir = $this->app->getDirectoryPath(App::DIR_CACHE)))) {
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
