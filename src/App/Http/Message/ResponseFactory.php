<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Di\Container as DiContainer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{
    private DiContainer $diContainer;

    public function __construct(DiContainer $diContainer)
    {
        $this->diContainer = $diContainer;
    }

    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->diContainer->create(ResponseInterface::class);
        return $response->withStatus($code, $reasonPhrase);
    }
}
