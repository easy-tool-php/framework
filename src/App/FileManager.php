<?php

namespace EasyTool\Framework\App;

use EasyTool\Framework\App\Exception\FileException;
use Exception;

class FileManager
{
    public const DIR_APP = 'app';
    public const DIR_CACHE = 'cache';
    public const DIR_CONFIG = 'config';
    public const DIR_LOG = 'log';
    public const DIR_MODULES = 'app/modules';
    public const DIR_PUB = 'pub';
    public const DIR_ROOT = 'root';
    public const DIR_TMP = 'tmp';
    public const DIR_VAR = 'var';

    private ?string $directoryRoot = null;

    public function initialize(string $directoryRoot): void
    {
        $this->directoryRoot = $directoryRoot;
    }

    /**
     * Get absolute path of directory with specified type,
     *     the types are defined as static variables of this class.
     *
     * @throws Exception
     */
    public function getDirectoryPath(string $type): string
    {
        switch ($type) {
            case self::DIR_ROOT:
                return $this->directoryRoot . '/';

            case self::DIR_APP:
                return $this->directoryRoot . '/app';

            case self::DIR_CONFIG:
                return $this->directoryRoot . '/app/config';

            case self::DIR_MODULES:
                return $this->directoryRoot . '/app/modules';

            case self::DIR_PUB:
                return $this->directoryRoot . '/pub';

            case self::DIR_VAR:
                return $this->directoryRoot . '/var';

            case self::DIR_CACHE:
                return $this->directoryRoot . '/var/cache';

            case self::DIR_LOG:
                return $this->directoryRoot . '/var/log';

            case self::DIR_TMP:
                return $this->directoryRoot . '/var/tmp';

            default:
                throw new FileException('Directory type is not supported.');
        }
    }

    /**
     * Get sub-folders of specified folder
     *
     * @throws Exception
     */
    public function getSubFolders(string $dir, $returnRelative = true, $recursion = false): array
    {
        if (is_dir($dir) && ($handler = opendir($dir))) {
            $subFolders = [];
            while (($file = readdir($handler)) !== false) {
                if ($file != '.' && $file != '..' && is_dir(($filePath = $dir . '/' . $file))) {
                    $subFolders[] = $returnRelative ? $file : $filePath;
                    if ($recursion) {
                        $subFolders = array_merge(
                            $subFolders,
                            $this->getSubFolders($filePath, true, true)
                        );
                    }
                }
            }
            closedir($handler);
            return $subFolders;
        }
        throw new FileException('Specified path is not a folder or could not be accessed.');
    }

    /**
     * Get file contents of specified path based on project root
     */
    public function getFileContents(string $filename): string
    {
        return file_get_contents($this->directoryRoot . '/' . $filename);
    }
}
