<?php

namespace EasyTool\Framework\App\Http\Message\Response;

use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Factory implements ResponseFactoryInterface
{
    private ObjectManager $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $this->objectManager->create(ResponseInterface::class);
        return $response->withStatus($code, $reasonPhrase);
    }
}
