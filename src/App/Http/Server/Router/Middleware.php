<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Http\Server\Router;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Http\Server\Request;
use EasyTool\Framework\App\Http\Server\Router\Route\Api as ApiRoute;
use EasyTool\Framework\App\Http\Server\Router\Route\Backend as BackendRoute;
use EasyTool\Framework\App\Http\Server\Router\Route\Frontend as FrontendRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Middleware implements MiddlewareInterface
{
    private Area $area;
    private ApiRoute $apiRoute;
    private BackendRoute $backendRoute;
    private FrontendRoute $frontendRoute;

    public function __construct(
        Area $area,
        ApiRoute $apiRoute,
        BackendRoute $backendRoute,
        FrontendRoute $frontendRoute
    ) {
        $this->area = $area;
        $this->apiRoute = $apiRoute;
        $this->backendRoute = $backendRoute;
        $this->frontendRoute = $frontendRoute;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request->getAttribute(Request::ACTION)) {
            if ($this->apiRoute->match($request)) {
                $this->area->setCode(Area::API);
            } elseif ($this->backendRoute->match($request)) {
                $this->area->setCode(Area::BACKEND);
            } elseif ($this->frontendRoute->match($request)) {
                $this->area->setCode(Area::FRONTEND);
            }
        }
        return $handler->handle($request);
    }
}
