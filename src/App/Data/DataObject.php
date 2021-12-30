<?php

namespace EasyTool\Framework\App\Data;

class DataObject
{
    private array $data;

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
    public function set(string $key, $value): DataObject
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Set whole data
     *
     * @param array $data  Data array, format is like ['key_a' => $valueA, 'key_b' => $valueB, ...]
     */
    public function setData(array $data): DataObject
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Append data, override when a key exists.
     *
     * @param array $data  Data array, format is like ['key_a' => $valueA, 'key_b' => $valueB, ...]
     */
    public function addData(array $data): DataObject
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
}
