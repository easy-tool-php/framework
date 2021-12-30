<?php

namespace EasyTool\Framework\App\Filesystem;

class FileManager
{
    private string $directoryRoot;

    public function initialize(string $directoryRoot): void
    {
        $this->directoryRoot = $directoryRoot;
    }

    public function getFileContents(string $filename): string
    {
        return file_get_contents($this->directoryRoot . '/' . $filename);
    }
}
