<?php

namespace EasyTool\Framework\App\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    /**
     * @inheritDoc
     */
    public function getProtocolVersion()
    {
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
    }

    /**
     * @inheritDoc
     */
    public function getHeaders()
    {
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name)
    {
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
    {
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
    }

    public function withoutHeader($name)
    {
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
    }
}
