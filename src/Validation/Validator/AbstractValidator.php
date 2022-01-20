<?php

namespace EasyTool\Framework\Validation\Validator;

abstract class AbstractValidator
{
    /**
     * Collect values from given data which match the specified path
     */
    protected function getMatchedValues(array $path, array $data): array
    {
        $values = [];

        $childPath = $path;
        $section = array_shift($childPath);

        if (count($path) > 1) {
            if ($section == '*') {
                if (is_array($data)) {
                    foreach ($data as $child) {
                        $values = array_merge($values, $this->getMatchedValues($childPath, $child));
                    }
                }
            } elseif (isset($data[$section])) {
                $values = array_merge($values, $this->getMatchedValues($childPath, $data[$section]));
            }
            return $values;
        }

        if ($section == '*') {
            if (is_array($data)) {
                foreach ($data as $child) {
                    $values[] = $child;
                }
            }
        } elseif (isset($data[$section])) {
            $values[] = $data[$section];
        }
        return $values;
    }

    /**
     * Validate given data of specified field
     */
    abstract public function validate(array $data, string $field, array $params = []): bool;
}
