<?php

namespace EasyTool\Framework\Validation\Validator;

class Options extends AbstractValidator
{
    public function validate(array $data, string $field, array $params = []): bool
    {
        try {
            $value = $this->getValue($field, $data);
        } catch (\Exception $e) {
            return true;
        }

        foreach (array_keys($value) as $option) {
            if (!in_array($option, $params)) {
                return false;
            }
        }
        return true;
    }
}
