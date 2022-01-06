<?php

namespace EasyTool\Framework\App\Http\Message;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    private $resource;

    public function __construct($resource = null)
    {
        $this->resource = is_resource($resource) ? $resource : fopen('php://temp', 'r+');
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $this->rewind();
        return $this->getContents();
    }

    /**
     * @inheritDoc
     */
    public function close()
    {
        fclose($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return strlen($this->__toString());
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return ftell($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return $this->getMetadata('seekable');
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): int
    {
        return fseek($this->resource, $offset, $whence);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): bool
    {
        return rewind($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return in_array($this->getMetadata('mode'), ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+']);
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        return fwrite($this->resource, $string);
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return in_array($this->getMetadata('mode'), ['r', 'r+', 'w+', 'a+', 'x+', 'c+']);
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        return stream_get_contents($this->resource, $length);
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        return stream_get_contents($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        $metaData = stream_get_meta_data($this->resource);
        return $key ? ($metaData[$key] ?? null) : $metaData;
    }
}
