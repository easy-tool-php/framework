<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Session\Adapter;

use EasyTool\Framework\App\Di\Container as DiContainer;
use Laminas\Cache\Storage\Adapter\Redis as Storage;
use Laminas\Session\SaveHandler\SaveHandlerInterface;

class Redis implements SaveHandlerInterface
{
    private Storage $storage;

    public function __construct(DiContainer $diContainer, array $options)
    {
        $this->storage = $diContainer->create(Storage::class, ['options' => $options]);
    }

    /**
     * @inheritDoc
     */
    public function open($path, $name): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc($maxLifetime): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function read($id): string
    {
        return $this->storage->getItem($id) ?? '';
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data): bool
    {
        $this->storage->setItem($id, $data);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy($id): bool
    {
        $this->storage->removeItem($id);
        return true;
    }
}
