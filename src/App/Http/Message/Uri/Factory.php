<?php

namespace EasyTool\Framework\App\Http\Message\Uri;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class Factory implements UriFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createUri(string $uri = ''): UriInterface
    {
    }
}
