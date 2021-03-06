<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\Validation\Validator;

class Required extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function validate(array $data, string $field, array $params = []): bool
    {
        return !empty($this->getMatchedValues(explode('.', $field), $data));
    }
}
