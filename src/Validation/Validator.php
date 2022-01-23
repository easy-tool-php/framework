<?php

namespace EasyTool\Framework\Validation;

use EasyTool\Framework\App\Exception\ClassException;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\Code\VariableTransformer;
use EasyTool\Framework\Validation\Exception\RuleNotFound;

class Validator
{
    public const RULE_SEPARATOR = '|';

    protected DiContainer $diContainer;
    protected VariableTransformer $variableTransformer;

    protected array $rules = [];
    protected array $data = [];

    public function __construct(
        DiContainer $diContainer,
        VariableTransformer $variableTransformer
    ) {
        $this->diContainer = $diContainer;
        $this->variableTransformer = $variableTransformer;
    }

    /**
     * Add a rule
     */
    public function addRule(string $field, $rule): Validator
    {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        $this->rules[$field][] = $rule;
        return $this;
    }

    /**
     * Rules format is like:
     * [
     *     'field_1' => 'validate_name',
     *     'field_2' => ['validate_name_1', 'validate_name_2' => ['param_1', 'param_2']]
     * ]
     */
    public function addRules(array $rules): Validator
    {
        foreach ($rules as $field => $ruleGroup) {
            if (is_array($ruleGroup)) {
                foreach ($ruleGroup as $index => $rule) {
                    $rule = is_numeric($index) ? $rule : [$index => $rule];
                    $this->addRule($field, $rule);
                }
            } else {
                $this->addRule($field, $ruleGroup);
            }
        }
        return $this;
    }

    /**
     * Set data to validate
     */
    public function setData($data): Validator
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Clean data and rules
     */
    public function reset(): Validator
    {
        $this->data = [];
        $this->rules = [];
        return $this;
    }

    /**
     * Parse a given rule, return a validation object and an array of parameters
     */
    protected function parseRule($rule): array
    {
        $validateName = $parameters = null;
        if (is_string($rule)) {
            $parameters = explode(self::RULE_SEPARATOR, $rule);
            $validateName = array_shift($parameters);
        } else {
            foreach ($rule as $validateName => $parameters) {
            }
        }
        switch ($validateName) {
            case 'required':
            case 'options':
                $validateClass = static::class . '\\' . $this->variableTransformer->snakeToHump($validateName);
                break;
            case 'array':
            case 'bool':
            case 'int':
            case 'numeric':
            case 'string':
                $validateClass = static::class . '\\IsTypeOf';
                $parameters = [$validateName];
                break;
            default:
                $validateClass = $validateName;
        }
        try {
            $validator = $this->diContainer->get($validateClass);
        } catch (ClassException $e) {
            throw new RuleNotFound(sprintf('Specified validator `%s` is not found.', $validateName));
        }
        return [$validator, $parameters];
    }

    /**
     * Validate
     */
    public function validate($rules = [], $data = []): bool
    {
        if (!empty($rules)) {
            $this->rules = [];
            $this->addRules($rules);
        }
        if (!empty($data)) {
            $this->data = $data;
        }
        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                [$validator, $parameters] = $this->parseRule($rule);
                if (!$validator->validate($this->data, $field, $parameters)) {
                    return false;
                }
            }
        }
        return true;
    }
}
