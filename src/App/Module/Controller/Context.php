<?php

namespace EasyTool\Framework\App\Module\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class Context
{
    private ServerRequestInterface $request;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ServerRequestInterface $request,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->request = $request;
        $this->responseFactory = $responseFactory;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }
}
