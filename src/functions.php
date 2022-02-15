<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

use EasyTool\Framework\App\Di\Container;

/**
 * This file contains shortcuts for executing some common methods in a quick way.
 */
function singleton($class)
{
    return Container::getInstance()->get($class);
}
