<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Module\Controller;

use EasyTool\Framework\App\Di\Container as DiContainer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class Context
{
    private DiContainer $diContainer;
    private ServerRequestInterface $request;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        DiContainer $diContainer,
        ServerRequestInterface $request,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->diContainer = $diContainer;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
    }

    public function getDiContainer(): DiContainer
    {
        return $this->diContainer;
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
