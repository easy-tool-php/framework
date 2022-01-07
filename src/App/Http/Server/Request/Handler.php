<?php

namespace EasyTool\Framework\App\Http\Server\Request;

use EasyTool\Framework\App\Module\Manager as ModuleManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements RequestHandlerInterface
{
    private ModuleManager $moduleManager;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        ModuleManager $moduleManager,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->moduleManager = $moduleManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseFactory->createResponse();
    }
}
