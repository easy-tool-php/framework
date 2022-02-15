<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\Filesystem;

use Exception;

class FileManager
{
    /**
     * Get files of specified folder
     *
     * @throws Exception
     */
    public function getFiles(string $dir, bool $returnRelative = true, bool $recursion = false): array
    {
        if (is_dir($dir) && ($handler = opendir($dir))) {
            $files = [];
            while (($file = readdir($handler)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                if (is_file(($filePath = $dir . '/' . $file))) {
                    $files[] = $returnRelative ? $file : $filePath;
                } elseif ($recursion && is_dir(($filePath = $dir . '/' . $file))) {
                    $files = array_merge(
                        $files,
                        $returnRelative ?
                            array_map(function ($child) use ($file) {
                                return $file . '/' . $child;
                            }, $this->getFiles($filePath, true, true))
                            : $this->getFiles($filePath, false, true)
                    );
                }
            }
            closedir($handler);
            return $files;
        }
        throw new Exception('Specified path is not a folder or could not be accessed.');
    }

    /**
     * Get sub-folders of specified folder
     *
     * @throws Exception
     */
    public function getSubFolders(string $dir, bool $returnRelative = true, bool $recursion = false): array
    {
        if (is_dir($dir) && ($handler = opendir($dir))) {
            $subFolders = [];
            while (($file = readdir($handler)) !== false) {
                if ($file != '.' && $file != '..' && is_dir(($filePath = $dir . '/' . $file))) {
                    $subFolders[] = $returnRelative ? $file : $filePath;
                    if ($recursion) {
                        $subFolders = array_merge(
                            $subFolders,
                            $returnRelative
                                ?
                                array_map(function ($child) use ($file) {
                                    return $file . '/' . $child;
                                }, $this->getSubFolders($filePath, true, true))
                                : $this->getSubFolders($filePath, false, true)
                        );
                    }
                }
            }
            closedir($handler);
            return $subFolders;
        }
        throw new Exception('Specified path is not a folder or could not be accessed.');
    }
}
