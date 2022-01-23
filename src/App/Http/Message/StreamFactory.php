<?php

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Di\Container as DiContainer;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    private DiContainer $diContainer;

    public function __construct(DiContainer $diContainer)
    {
        $this->diContainer = $diContainer;
    }

    /**
     * @inheritDoc
     */
    public function createStream(string $content = ''): StreamInterface
    {
        return $this->diContainer->create(StreamInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->diContainer->create(StreamInterface::class, [
            'resource' => fopen($filename, $mode)
        ]);
    }

    /**
     * @inheritDoc
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->diContainer->create(StreamInterface::class, ['resource' => $resource]);
    }
}
