<?php

namespace EasyTool\Framework\App\Module\Controller;

use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class Context
{
    private ObjectManager $objectManager;
    private ServerRequestInterface $request;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ObjectManager $objectManager,
        ServerRequestInterface $request,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
    }

    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
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
