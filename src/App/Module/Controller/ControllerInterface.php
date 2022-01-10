<?php

namespace EasyTool\Framework\App\Module\Controller;

use Psr\Http\Message\ResponseInterface;

interface ControllerInterface
{
    public function execute(): ResponseInterface;
}
