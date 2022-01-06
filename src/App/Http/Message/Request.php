<?php

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Http\Message;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request extends Message implements RequestInterface
{
    public const METHOD_GET = 'GET';
    public const METHOD_PUT = 'PUT';
    public const METHOD_POST = 'POST';
    public const METHOD_DELETE = 'DELETE';

    private string $method;
    private ?string $requestTarget;
    private UriInterface $uri;

    public function __construct($method)
    {
        $this->method = $method;
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget()
    {
        return $this->requestTarget;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget): Request
    {
        $this->requestTarget = $requestTarget;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $this->uri = $uri;
        return $this;
    }
}
