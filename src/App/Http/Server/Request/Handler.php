<?php

namespace EasyTool\Framework\App\Http\Server\Request;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Http\Server\Request;
use EasyTool\Framework\App\ObjectManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements RequestHandlerInterface
{
    public const CONFIG_MIDDLEWARES = 'middlewares';

    private ObjectManager $objectManager;
    private ResponseFactoryInterface $responseFactory;

    private array $middlewares;

    public function __construct(
        Config $config,
        ObjectManager $objectManager,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->objectManager = $objectManager;
        $this->responseFactory = $responseFactory;

        $this->middlewares = $config->get(null, self::CONFIG_MIDDLEWARES);
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewares)) {
            if (($action = $request->getAttribute(Request::ACTION))) {
                return call_user_func($action);
            }
            return $this->responseFactory->createResponse(404);
        }

        /** @var MiddlewareInterface $middleware */
        $middleware = $this->objectManager->create(array_shift($this->middlewares));
        return $middleware->process($request, $this);
    }
}
