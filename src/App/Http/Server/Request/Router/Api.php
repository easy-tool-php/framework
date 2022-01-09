<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Config;
use Psr\Http\Message\ServerRequestInterface;

class Api extends AbstractRouter
{
    public const CONFIG_NAME = 'env';
    public const CONFIG_PATH = 'api/route';

    private Config $config;

    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config = $config;
        parent::__construct($context);
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

        $routes = $this->moduleManager->getApiRoutes();
        foreach (array_keys($routes) as $route) {

        }

        $this->area->setCode(Area::API);
    }
}
