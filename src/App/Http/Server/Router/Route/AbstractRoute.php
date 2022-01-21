<?php

namespace EasyTool\Framework\App\Http\Server\Router\Route;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Module\Controller\ControllerInterface;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Code\VariableTransformer;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRoute
{
    protected Config $config;
    protected ModuleManager $moduleManager;
    protected ObjectManager $objectManager;
    protected VariableTransformer $variableTransformer;

    public function __construct(
        Config $config,
        ModuleManager $moduleManager,
        ObjectManager $objectManager,
        VariableTransformer $variableTransformer
    ) {
        $this->config = $config;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->variableTransformer = $variableTransformer;
    }

    protected function getModuleByRoute($areaCode, $routeName): ?array
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

    protected function getActionInstance($areaCode, $routeName, $controllerName, $actionName): ?object
    {
        if (!($moduleConfig = $this->getModuleByRoute($areaCode, $routeName))) {
            return null;
        }

        $class = '\\' . $moduleConfig[ModuleManager::MODULE_NAMESPACE]
            . 'Controller\\' . ucfirst($areaCode) . '\\'
            . str_replace('/', '\\', $this->variableTransformer->snakeToHump($controllerName)) . '\\'
            . str_replace('/', '\\', $this->variableTransformer->snakeToHump($actionName));

        if (!class_exists($class)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($class);
        $reflectionClass->implementsInterface(ControllerInterface::class);

        return $reflectionClass->implementsInterface(ControllerInterface::class)
            ? $this->objectManager->create($class) : null;
    }

    abstract public function match(ServerRequestInterface $request): bool;
}
