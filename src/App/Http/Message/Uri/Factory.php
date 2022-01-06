<?php

namespace EasyTool\Framework\App\Http\Message\Uri;

use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class Factory implements UriFactoryInterface
{
    private ObjectManager $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return $this->objectManager->create(UriInterface::class, ['uri' => $uri]);
    }
}
