<?php

namespace EasyTool\Framework\App\Http\Server\Request;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Data\DataObject;
use EasyTool\Framework\App\Event\Manager as EventManager;
use EasyTool\Framework\App\Http\Server\Request\Router\Api as ApiRouter;
use EasyTool\Framework\App\Http\Server\Request\Router\Backend as BackendRouter;
use EasyTool\Framework\App\Http\Server\Request\Router\Frontend as FrontendRouter;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Handler implements RequestHandlerInterface
{
    private Area $area;
    private ApiRouter $apiRouter;
    private BackendRouter $backendRouter;
    private FrontendRouter $frontendRouter;
    private EventManager $eventManager;
    private ModuleManager $moduleManager;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        Area $area,
        EventManager $eventManager,
        ModuleManager $moduleManager,
        ResponseFactoryInterface $responseFactory,
        ApiRouter $apiRouter,
        BackendRouter $backendRouter,
        FrontendRouter $frontendRouter
    ) {
        $this->area = $area;
        $this->apiRouter = $apiRouter;
        $this->backendRouter = $backendRouter;
        $this->frontendRouter = $frontendRouter;
        $this->eventManager = $eventManager;
        $this->moduleManager = $moduleManager;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->eventManager->dispatch(new DataObject(['name' => 'before_route', 'request' => $request]));

        if (
            $this->apiRouter->match($request)
            || $this->backendRouter->match($request)
            || $this->frontendRouter->match($request)
        ) {
        }

        return $this->responseFactory->createResponse();
    }
}
