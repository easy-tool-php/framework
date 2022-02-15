<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Data;

use InvalidArgumentException;

trait MultiLevelsStructure
{
    /**
     * Get child by specified path of given data
     */
    private function getChildByPath(array $path, array &$data, string $currentPath = '')
    {
        $tmp = $path;
        $section = array_shift($tmp);
        $currentPath .= '/' . $section;
        if (!isset($data[$section])) {
            throw new InvalidArgumentException(sprintf('Path `%s` does not exist.', $currentPath));
        }
        return isset($tmp[0])
            ? $this->getChildByPath($tmp, $data[$section], $currentPath)
            : $data[$section];
    }

    /**
     * Set child by specified path of given data
     */
    private function setChildByPath(array $path, array &$data, $value, string $currentPath = ''): self
    {
        $tmp = $path;
        $section = array_shift($tmp);
        $currentPath .= '/' . $section;
        if (isset($tmp[0])) {
            if (!isset($data[$section])) {
                $data[$section] = [];
            }
            if (!is_array($data[$section])) {
                throw new Exception(sprintf('Path `%s` does not have a child.', $currentPath));
            }
            $this->setChildByPath($tmp, $data[$section], $value, $currentPath);
        } else {
            $data[$section] = $value;
        }
        return $this;
    }
}
