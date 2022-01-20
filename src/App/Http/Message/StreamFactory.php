<?php

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    private ObjectManager $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return $this->objectManager->create(StreamInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->objectManager->create(StreamInterface::class, [
            'resource' => fopen($filename, $mode)
        ]);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->objectManager->create(StreamInterface::class, ['resource' => $resource]);
    }
}
