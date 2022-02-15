<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Session\Adapter;

use EasyTool\Framework\App\Filesystem\Directory;
use Laminas\Session\SaveHandler\SaveHandlerInterface;

class Files implements SaveHandlerInterface
{
    private string $dir;

    public function __construct(Directory $directory)
    {
        $this->dir = $directory->getDirectoryPath(Directory::VAR) . '/session';
    }

    /**
     * Get filename with specified ID
     */
    private function getFilename($id): string
    {
        return $this->dir . '/' . $id;
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
        $filename = $this->getFilename($id);
        if (is_file($filename)) {
            return file_get_contents($filename);
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data): bool
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0755, true);
        }
        file_put_contents($this->getFilename($id), $data);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy($id): bool
    {
        unlink($this->getFilename($id));
        return true;
    }
}
