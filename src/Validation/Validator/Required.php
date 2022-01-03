<?php

namespace EasyTool\Framework\Validation\Validator;

class Required extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function validate(array $data, string $field, array $params = []): bool
    {
        try {
            $this->getValue($field, $data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
