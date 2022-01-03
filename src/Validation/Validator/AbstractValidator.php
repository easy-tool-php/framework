<?php

namespace EasyTool\Framework\Validation\Validator;

use EasyTool\Framework\Validation\Exception\FieldNotFound;

abstract class AbstractValidator
{
    /**
     * Parse field
     */
    protected function getValue($field, $data)
    {
        $path = explode('.', $field);
        $tmp = $data;
        foreach ($path as $section) {
            if (isset($tmp[$section])) {
                $tmp = $tmp[$section];
            } else {
                throw new FieldNotFound(sprintf('Section `%s` does not exist.', $section));
            }
        }
        return $tmp;
    }

    /**
     * Validate given data of specified field
     */
    abstract public function validate(array $data, string $field, array $params = []): bool;
}
