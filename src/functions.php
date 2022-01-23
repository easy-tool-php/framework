<?php

use EasyTool\Framework\App\Di\Container;

/**
 * This file contains shortcuts for executing some common methods in a quick way.
 */
function singleton($class)
{
    Container::getInstance()->get($class);
}
