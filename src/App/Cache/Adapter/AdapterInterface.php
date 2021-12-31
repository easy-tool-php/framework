<?php

namespace EasyTool\Framework\App\Cache\Adapter;

interface AdapterInterface
{
    public function load(string $cacheName): array;

    public function save(string $cacheName, array $data): void;
}
