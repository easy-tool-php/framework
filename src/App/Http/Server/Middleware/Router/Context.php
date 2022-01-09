<?php

namespace EasyTool\Framework\App\Http\Server\Request\Router;

use EasyTool\Framework\App\Area;
use EasyTool\Framework\App\Module\Manager as ModuleManager;
use EasyTool\Framework\Code\VariableTransformer;

class Context
{
    private Area $area;
    private ModuleManager $moduleManager;
    private VariableTransformer $variableTransformer;

    public function __construct(
        Area $area,
        ModuleManager $moduleManager,
        VariableTransformer $variableTransformer
    ) {
        $this->area = $area;
        $this->moduleManager = $moduleManager;
        $this->variableTransformer = $variableTransformer;
    }

    public function getArea(): Area
    {
        return $this->area;
    }

    public function getModuleManager(): ModuleManager
    {
        return $this->moduleManager;
    }

    public function getVariableTransformer(): VariableTransformer
    {
        return $this->variableTransformer;
    }
}
