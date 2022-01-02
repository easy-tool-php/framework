<?php

namespace EasyTool\Framework\Validation;

use EasyTool\Framework\App\ObjectManager;

class Validator
{
    public const RULE_SEPARATOR = '|';

    protected ObjectManager $objectManager;
    protected array $rules = [];
    protected array $data = [];

    public function __construct(
        ObjectManager $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Add a rule
     */
    public function addRule(string $field, string $rule): Validator
    {
        if (!isset($this->rules[$field])) {
            $this->rules[$field] = [];
        }
        $this->rules[$field][] = $rule;
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
     * Parse a given rule, return a validation object and an array of parameters
     */
    protected function parseRule(string $rule): array
    {
        $parameters = explode(self::RULE_SEPARATOR, $rule);
        $validator = array_shift($parameters);
        return [$this->objectManager->get($validator), $parameters];
    }

    /**
     * Validate
     */
    public function validate(): bool
    {
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
