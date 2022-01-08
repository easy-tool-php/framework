<?php

namespace EasyTool\Framework\App\Config;

use Exception;

class Config
{
    public function get($path, $namespace = '')
    {
        return $this->getChild(explode('/', $path), $this->data);
    }

    public function set($path, $value): self
    {
        $this->setChild(explode('/', $path), $this->data, $value);
        return $this;
    }

    private function getChild(array $path, &$data, $currentPath = '')
    {
        $tmp = $path;
        $section = array_shift($tmp);
        $currentPath .= '/' . $section;
        if (!isset($data[$section])) {
            throw new Exception(sprintf('Path `%s` does not exist.', $currentPath));
        }
        return isset($tmp[0])
            ? $this->getChild($tmp, $data[$section], $currentPath)
            : $data[$section];
    }

    private function setChild(array $path, array &$data, $value, $currentPath = ''): self
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
            $this->getChild($tmp, $data[$section], $value, $currentPath);
        } else {
            $data[$section] = $value;
        }
        return $this;
    }
}
