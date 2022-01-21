<?php

namespace EasyTool\Framework\App\ObjectManager\Config;

use EasyTool\Framework\App\Config\AbstractCollector;

class Collector extends AbstractCollector
{
    public function validate(array $config): bool
    {
        return $this->validator->validate(['*' => ['required', 'string']]);
    }
}
