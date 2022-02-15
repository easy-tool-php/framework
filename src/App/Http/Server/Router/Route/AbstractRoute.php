<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Http\Server\Router\Route;

use EasyTool\Framework\App\Env\Config as EnvConfig;
use EasyTool\Framework\App\Module\Controller\ControllerInterface;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\Code\VariableTransformer;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRoute
{
    protected EnvConfig $envConfig;
    protected ModuleManager $moduleManager;
    protected DiContainer $diContainer;
    protected VariableTransformer $variableTransformer;

    public function __construct(
        EnvConfig $envConfig,
        ModuleManager $moduleManager,
        DiContainer $diContainer,
        VariableTransformer $variableTransformer
    ) {
        $this->envConfig = $envConfig;
        $this->moduleManager = $moduleManager;
        $this->diContainer = $diContainer;
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
            ? $this->diContainer->create($class) : null;
    }

    abstract public function match(ServerRequestInterface $request): bool;
}
