<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Http\Server\Router\Route;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Http\Server\Request;
use Psr\Http\Message\ServerRequestInterface;

class Frontend extends AbstractRoute
{
    /**
     * Check whether the request path has a matched frontend controller
     */
    public function match(ServerRequestInterface $request): bool
    {
        [$routeName, $controllerName, $actionName] = array_pad(
            explode('/', trim($request->getUri()->getPath(), '/')),
            3,
            'index'
        );
        if (($action = $this->getActionInstance(Area::FRONTEND, $routeName, $controllerName, $actionName))) {
            $request->withAttribute(Request::ACTION, [$action, 'execute']);
        }
        return true;
    }
}
