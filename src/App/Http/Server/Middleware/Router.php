<?php

namespace EasyTool\Framework\App\Http\Server\Middleware;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Module\Controller\ControllerInterface;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Code\VariableTransformer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;

class Router implements MiddlewareInterface
{
    public const ACTION = 'action';

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

        $reflectionClass = new ReflectionClass('\\' . $class);
        $reflectionClass->implementsInterface(ControllerInterface::class);

        return $reflectionClass->implementsInterface(ControllerInterface::class)
            ? $this->objectManager->create($class) : null;
    }

    /**
     * Check whether the request path matches an API route
     */
    private function matchApi(ServerRequestInterface $request): bool
    {
        [$route, $path] = explode('/', trim($request->getUri()->getPath(), '/'), 2);
        if ($route != $this->config->get(self::CONFIG_API_PATH, self::CONFIG_NAME)) {
            return false;
        }

        $routes = $this->moduleManager->getApiRoutes();
        foreach ($routes as $route => $action) {
            [$method, $apiPath] = explode(':', $route, 2);
            $regex = preg_replace('/:\w+/', '\\w+', str_replace('/', '\/', $apiPath));
            if ($method == $request->getMethod() && preg_match('/^' . $regex . '$/', $path)) {
                $this->area->setCode(Area::API);
                $request->withAttribute(
                    self::ACTION,
                    [$this->objectManager->create($action['class']), $action['method']]
                );
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether the request path has a matched backend controller
     */
    private function matchBackend(ServerRequestInterface $request): bool
    {
        [$route, $path] = explode('/', trim($request->getUri()->getPath(), '/'), 2);
        if ($route != $this->config->get(self::CONFIG_BACKEND_PATH, self::CONFIG_NAME)) {
            return false;
        }

        [$routeName, $controllerName, $actionName] = array_pad(explode('/', $path), 3, 'index');
        if (($action = $this->getActionInstance(Area::BACKEND, $routeName, $controllerName, $actionName))) {
            $this->area->setCode(Area::BACKEND);
            $request->withAttribute(self::ACTION, [$action, 'execute']);
        }
        return true;
    }

    /**
     * Check whether the request path has a matched frontend controller
     */
    private function matchFrontend(ServerRequestInterface $request): bool
    {
        [$routeName, $controllerName, $actionName] = array_pad(
            explode('/', trim($request->getUri()->getPath(), '/')),
            3,
            'index'
        );
        if (($action = $this->getActionInstance(Area::BACKEND, $routeName, $controllerName, $actionName))) {
            $this->area->setCode(Area::FRONTEND);
            $request->withAttribute(self::ACTION, [$action, 'execute']);
        }
        return true;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request->getAttribute(self::ACTION)) {
            $this->matchApi($request)
            || $this->matchBackend($request)
            || $this->matchFrontend($request);
        }
        return $handler->handle($request);
    }
}
