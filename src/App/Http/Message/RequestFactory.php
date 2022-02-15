<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Di\Container as DiContainer;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriFactoryInterface;

class RequestFactory implements RequestFactoryInterface
{
    private DiContainer $diContainer;
    private UriFactoryInterface $uriFactory;

    public function __construct(
        DiContainer $diContainer,
        UriFactoryInterface $uriFactory
    ) {
        $this->diContainer = $diContainer;
        $this->uriFactory = $uriFactory;
    }

    /**
     * @inheritDoc
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        /** @var RequestInterface $request */
        $request = $this->diContainer->create(RequestInterface::class);
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }
        return $request->withMethod($method)->withUri($uri);
    }
}
