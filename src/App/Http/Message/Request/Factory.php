<?php

namespace EasyTool\Framework\App\Http\Message\Request;

use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class Factory implements RequestFactoryInterface
{
    private ObjectManager $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        $this->objectManager->create(RequestInterface::class);
    }
}
