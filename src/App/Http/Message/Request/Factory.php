<?php

namespace EasyTool\Framework\App\Http\Message\Request;

use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriFactoryInterface;

class Factory implements RequestFactoryInterface
{
    private ObjectManager $objectManager;
    private UriFactoryInterface $uriFactory;

    public function __construct(
        ObjectManager $objectManager,
        UriFactoryInterface $uriFactory
    ) {
        $this->objectManager = $objectManager;
        $this->uriFactory = $uriFactory;
    }

    /**
     * @inheritDoc
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        /** @var RequestInterface $request */
        $request = $this->objectManager->create(RequestInterface::class);
        if (is_string($uri)) {
            $uri = $this->uriFactory->createUri($uri);
        }
        return $request->withMethod($method)->withUri($uri);
    }
}
