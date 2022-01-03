<?php

namespace EasyTool\Framework\Validation\Validator;

class IsArray extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function validate(array $data, string $field, array $params = []): bool
    {
        try {
            $value = $this->getValue($field, $data);
        } catch (\Exception $e) {
            return true;
        }

        return is_array($value);
    }
}
