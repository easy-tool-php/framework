<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Data;

class DataObject
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get data with a specified key
     *
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Set data with a specified key
     *
     * @param mixed $value
     */
    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get whole data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set whole data
     *
     * @param array $data Data array, format is like ['key_a' => $valueA, 'key_b' => $valueB, ...]
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Append data, override when a key exists.
     *
     * @param array $data Data array, format is like ['key_a' => $valueA, 'key_b' => $valueB, ...]
     */
    public function addData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Return an array with pure data, no objects
     */
    public function toArray($data = null): array
    {
        if ($data === null) {
            $data = $this->data;
        }

        $array = [];
        foreach ($data as $key => $value) {
            switch (strtolower(gettype($value))) {
                case 'integer':
                case 'double':
                case 'string':
                case 'null':
                case 'boolean':
                    $array[$key] = $value;
                    break;

                case 'array':
                    $array[$key] = $this->toArray($value);
                    break;
            }
        }

        return $array;
    }
}
