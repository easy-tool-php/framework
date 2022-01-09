<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Config;
use Psr\Http\Message\ServerRequestInterface;

class Backend extends AbstractRouter
{
    public const CONFIG_NAME = 'env';
    public const CONFIG_PATH = 'backend/route';

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

        [$routeName, $controllerName, $actionName] = array_pad(explode('/', $path), 3, 'index');

        if (($actionInstance = $this->getActionInstance(Area::BACKEND, $routeName, $controllerName, $actionName))) {
        }

        $request
            ->withAttribute('route', $routeName)
            ->withAttribute('controller', $controllerName)
            ->withAttribute('action', $actionName);

        $this->area->setCode(Area::BACKEND);
    }
}
