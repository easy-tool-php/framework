<?php

namespace EasyTool\Framework\App\Http\Server\Middleware;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Code\VariableTransformer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Router implements MiddlewareInterface
{
    public const CONFIG_NAME = 'env';
    public const CONFIG_API_PATH = 'api/route';
    public const CONFIG_BACKEND_PATH = 'backend/route';

    private Area $area;
    private Config $config;
    private ModuleManager $moduleManager;
    private ObjectManager $objectManager;
    private VariableTransformer $variableTransformer;

    public function __construct(
        Area $area,
        Config $config,
        ModuleManager $moduleManager,
        ObjectManager $objectManager,
        VariableTransformer $variableTransformer
    ) {
        $this->area = $area;
        $this->config = $config;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->variableTransformer = $variableTransformer;
    }

    private function getModuleByRoute($areaCode, $routeName): ?array
    {
        foreach ($this->moduleManager->getEnabledModules() as $moduleConfig) {
            if (
                isset($moduleConfig[ModuleManager::MODULE_ROUTE][$areaCode])
                && $routeName == $moduleConfig[ModuleManager::MODULE_ROUTE][$areaCode]
            ) {
                return $moduleConfig;
            }
        }
        return null;
    }

    private function getActionInstance($areaCode, $routeName, $controllerName, $actionName): ?object
    {
        if (!($moduleConfig = $this->getModuleByRoute($areaCode, $routeName))) {
            return null;
        }

        $class = $moduleConfig[ModuleManager::MODULE_NAMESPACE]
            . 'Controller\\' . ucfirst($areaCode) . '\\'
            . str_replace('/', '\\', $this->variableTransformer->snakeToHump($controllerName)) . '\\'
            . str_replace('/', '\\', $this->variableTransformer->snakeToHump($actionName));

        return class_exists($class) ? $this->objectManager->create($class) : null;
    }

    private function matchApi($path)
    {
        $routes = $this->moduleManager->getApiRoutes();
        foreach (array_keys($routes) as $route) {
            [$method, $path] = explode(':', $route);
        }
    }

    private function matchBackend($path)
    {
        [$routeName, $controllerName, $actionName] = array_pad(explode('/', $path), 3, 'index');
        if ($action = $this->getActionInstance(Area::BACKEND, $routeName, $controllerName, $actionName)) {
            print_r($action);
        }
    }

    private function matchFrontend($request)
    {
        [$routeName, $controllerName, $actionName] = array_pad(
            explode('/', trim($request->getUri()->getPath(), '/')),
            3,
            'index'
        );
        $this->getActionInstance(Area::FRONTEND, $routeName, $controllerName, $actionName);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        [$route, $path] = explode('/', trim($request->getUri()->getPath(), '/'), 2);

        ($route == $this->config->get(self::CONFIG_API_PATH, self::CONFIG_NAME) && $this->matchApi($path))
        || ($route == $this->config->get(self::CONFIG_BACKEND_PATH, self::CONFIG_NAME) && $this->matchBackend($path))
        || $this->matchFrontend($request);

        return $handler->handle($request);
    }
}
