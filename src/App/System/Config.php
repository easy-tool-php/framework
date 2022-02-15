<?php

namespace EasyTool\Framework\App\System;

use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'system.php';
    protected array $format = [];
}
