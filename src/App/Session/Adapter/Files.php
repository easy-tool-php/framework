<?php

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

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function destroy($id)
    {
        // TODO: Implement destroy() method.
    }

    public function gc($max_lifetime)
    {
        // TODO: Implement gc() method.
    }

    public function open($path, $name)
    {
        // TODO: Implement open() method.
    }

    public function read($id)
    {
        if (is_file($this->dir . '/' . $id)) {
        }
    }

    public function write($id, $data)
    {
        // TODO: Implement write() method.
    }
}
