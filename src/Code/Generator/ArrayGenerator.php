<?php

namespace EasyTool\Framework\Code\Generator;

use Laminas\Code\Generator\AbstractGenerator;

class ArrayGenerator extends AbstractGenerator
{
    private array $array = [];

    /**
     * Create a new array generator with an array
     */
    public static function fromArray(array $array): self
    {
        return (new static())->setArray($array);
    }

    /**
     * Set the source array
     */
    public function setArray(array $array): self
    {
        $this->array = $array;
        return $this;
    }

    /**
     * Transfer a given array to a string
     */
    protected function arrayToString(array $data, int $level = 1, string $space = '    '): string
    {
        $prefix = str_repeat($space, $level);

        $arrString = [];
        foreach ($data as $key => $value) {
            switch (strtolower(gettype($value))) {
                case 'integer':
                case 'double':
                    $value = $value;
                    break;

                case 'string':
                    $value = '\'' . str_replace('\'', '\\\'', $value) . '\'';
                    break;

                case 'null':
                    $value = 'null';
                    break;

                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;

                case 'array':
                    $value = $this->arrayToString($value, $level + 1);
                    break;
            }
            $arrString[] = $prefix . sprintf('\'%s\' => %s', str_replace('\'', '\\\'', $key), $value);
        }

        return sprintf("[\n%s\n%s]", implode(",\n", $arrString), str_repeat(' ', ($level - 1) * 4));
    }

    /**
     * @inheritDoc
     */
    public function generate()
    {
        return $this->arrayToString($this->array);
    }
}
