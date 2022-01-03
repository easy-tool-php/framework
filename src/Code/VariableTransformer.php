<?php

namespace EasyTool\Framework\Code;

class VariableTransformer
{
    /**
     * Transform a variable from hump format to snake format
     */
    public function humpToSnake(string $varName): string
    {
        return strtolower(ltrim(preg_replace('/([A-X])/', '_$1', $varName), '_'));
    }

    /**
     * Transform a variable from snake format to hump format
     */
    public function snakeToHump(string $varName): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $varName)));
    }
}
