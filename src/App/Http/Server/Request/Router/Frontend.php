<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use EasyTool\Framework\App\Area;
use Psr\Http\Message\ServerRequestInterface;

class Frontend implements RouterInterface
{
    private Area $area;

    public function __construct(
        Area $area
    ) {
        $this->area = $area;
    }

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

        $this->area->setCode(Area::FRONTEND);
    }
}
