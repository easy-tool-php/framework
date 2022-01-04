<?php

namespace EasyTool\Framework\Validation\Validator;

class Options extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function validate(array $data, string $field, array $params = []): bool
    {
        if (empty(($values = $this->getMatchedValues(explode('.', $field), $data)))) {
            return true;
        }

        foreach ($values as $value) {
            foreach (array_keys($value) as $option) {
                if (!in_array($option, $params)) {
                    return false;
                }
            }
        }
        return true;
    }
}