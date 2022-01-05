<?php

namespace EasyTool\Framework\App\Http\Message\Response;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Factory implements ResponseFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
    }
}
