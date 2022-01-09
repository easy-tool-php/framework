<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\Code\VariableTransformer;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractRouter implements RouterInterface
{
    protected Area $area;
    protected ModuleManager $moduleManager;
    protected VariableTransformer $variableTransformer;

    public function __construct(Context $context)
    {
        $this->area = $context->getArea();
        $this->moduleManager = $context->getModuleManager();
        $this->variableTransformer = $context->getVariableTransformer();
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

    protected function getActionInstance($areaCode, $routeName, $controllerName, $actionName)
    {
        if (!($moduleConfig = $this->getModuleByRoute($areaCode, $routeName))) {
            return null;
        }
        $class = $moduleConfig[ModuleManager::MODULE_NAMESPACE]
            . 'Controller\\' . ucfirst($areaCode) . '\\'
            . str_replace('/', '\\', $this->variableTransformer->snakeToHump($controllerName)) . '\\'
            . str_replace('/', '\\', $this->variableTransformer->snakeToHump($actionName));

        echo $class;
    }

    /**
     * @inheritDoc
     */
    abstract public function match(ServerRequestInterface $request);
}
