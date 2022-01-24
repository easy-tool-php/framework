<?php

namespace EasyTool\Framework\App\Event;

use EasyTool\Framework\App\Config\AbstractFileConfig;

class Config extends AbstractFileConfig
{
    protected string $filename = 'events.php';
    protected array $format = [
        '*.*.listener' => ['required', 'string'],
        '*.*.order'    => ['int']
    ];
}
