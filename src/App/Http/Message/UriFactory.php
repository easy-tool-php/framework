<?php

namespace EasyTool\Framework\App\Http\Message;

use EasyTool\Framework\App\Di\Container as DiContainer;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
    private DiContainer $diContainer;

    public function __construct(DiContainer $diContainer)
    {
        $this->diContainer = $diContainer;
    }

    /**
     * @inheritDoc
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return $this->diContainer->create(UriInterface::class, ['uri' => $uri]);
    }
}
