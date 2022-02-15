<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\Validation\Validator;

class IsTypeOf extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function validate(array $data, string $field, array $params = []): bool
    {
        if (empty(($values = $this->getMatchedValues(explode('.', $field), $data)))) {
            return true;
        }

        [$type] = $params;
        foreach ($values as $value) {
            if (!('is_' . $type)($value)) {
                return false;
            }
        }
        return true;
    }
}
