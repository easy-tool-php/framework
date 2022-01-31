<?php

namespace EasyTool\Framework\App\Http\Server\Request;

use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\App\Http\Server\Request;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements RequestHandlerInterface
{
    private DiContainer $diContainer;
    private ResponseFactoryInterface $responseFactory;
    private array $middlewares;

    public function __construct(
        Config $config,
        DiContainer $diContainer,
        Directory $directory,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->diContainer = $diContainer;
        $this->responseFactory = $responseFactory;

        // Middlewares in the pool are defined in `app/config/middlewares.php`.
        $this->middlewares = $config->collectData($directory->getDirectoryPath(Directory::CONFIG));
    }

    /**
     * Handle HTTP request with predefined middlewares
     *
     * A processed middleware will be shifted from the pool.
     * Each middleware MUST execute `handle` method of this handler,
     *     unless it needs to skip all next ones.
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
        $middleware = $this->diContainer->create(array_shift($this->middlewares));
        return $middleware->process($request, $this);
    }
}
