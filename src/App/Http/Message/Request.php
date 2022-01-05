<?php

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Http\Message;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    /**
     * @inheritDoc
     */
    public function getRequestTarget()
    {
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method)
    {
    }

    /**
     * @inheritDoc
     */
    public function getUri()
    {
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
    }
}
