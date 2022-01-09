<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use EasyTool\Framework\App\Area;
use Psr\Http\Message\ServerRequestInterface;

class Frontend extends AbstractRouter
{
    /**
     * @inheritDoc
     */
    public function match(ServerRequestInterface $request)
    {
        [$routeName, $controllerName, $actionName] = array_pad(
            explode('/', trim($request->getUri()->getPath(), '/')),
            3,
            'index'
        );

        if (($actionInstance = $this->getActionInstance(Area::FRONTEND, $routeName, $controllerName, $actionName))) {
        }

        $request
            ->withAttribute('route', $routeName)
            ->withAttribute('controller', $controllerName)
            ->withAttribute('action', $actionName);

        $this->area->setCode(Area::FRONTEND);
    }
}
