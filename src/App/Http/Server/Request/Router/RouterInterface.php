<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * Parse request
     */
    public function match(ServerRequestInterface $request);
}
