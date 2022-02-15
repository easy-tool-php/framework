<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Module\Controller;

use Psr\Http\Message\ResponseInterface;

interface ControllerInterface
{
    public function execute(): ResponseInterface;
}
