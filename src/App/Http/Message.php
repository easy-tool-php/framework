<?php

namespace EasyTool\Framework\App\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
    public const PROTOCOL_VERSION = '1.1';

    private StreamInterface $body;
    private string $protocolVersion = self::PROTOCOL_VERSION;
    private array $headers = [];

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version): Message
    {
        $this->protocolVersion = $version;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name): string
    {
        return implode(',', $this->getHeader(strtolower($name)));
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value): Message
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $this->headers[strtolower($name)] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value): Message
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $name = strtolower($name);
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = [];
        }
        $this->headers[$name] = array_merge($this->headers[$name], $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name): Message
    {
        unset($this->headers[strtolower($name)]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body): Message
    {
        $this->body = $body;
        return $this;
    }
}
