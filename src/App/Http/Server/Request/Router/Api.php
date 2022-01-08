<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Config;
use Psr\Http\Message\ServerRequestInterface;

class Api implements RouterInterface
{
    public const CONFIG_NAME = 'env';
    public const CONFIG_PATH = 'api/route';

    private Area $area;
    private Config $config;

    public function __construct(
        Area $area,
        Config $config
    ) {
        $this->area = $area;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function match(ServerRequestInterface $request)
    {
        [$route, $path] = explode('/', trim($request->getUri()->getPath(), '/'), 2);
        if ($route != $this->config->get(self::CONFIG_PATH, self::CONFIG_NAME)) {
            return false;
        }
        [$routeName, $controllerName, $actionName] = array_pad(explode('/', $path), 3, 'index');

        $this->area->setCode(Area::API);
    }
}
