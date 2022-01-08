<?php

namespace EasyTool\Framework\App\Module\Controller;

use Psr\Http\Message\ServerRequestInterface;

class Context
{
    private ServerRequestInterface $request;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
