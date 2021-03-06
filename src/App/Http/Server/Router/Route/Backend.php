<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Http\Server\Router\Route;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Http\Server\Request;
use Psr\Http\Message\ServerRequestInterface;

class Backend extends AbstractRoute
{
    public const ENV_PATH = 'backend/route';

    /**
     * Check whether the request path has a matched backend controller
     */
    public function match(ServerRequestInterface $request): bool
    {
        [$prefix, $path] = array_pad(explode('/', trim($request->getUri()->getPath(), '/'), 2), 2, null);
        if ($prefix != $this->envConfig->get(self::ENV_PATH)) {
            return false;
        }

        [$routeName, $controllerName, $actionName] = array_pad(explode('/', $path), 3, 'index');
        if (($action = $this->getActionInstance(Area::BACKEND, $routeName, $controllerName, $actionName))) {
            $request->withAttribute(Request::ACTION, [$action, 'execute']);
        }
        return true;
    }
}
