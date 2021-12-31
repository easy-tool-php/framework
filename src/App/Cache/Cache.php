<?php

namespace EasyTool\Framework\App\Cache;

use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    protected Adapter\AdapterInterface $adapter;
    protected array $data = [];

    public function __construct(Adapter\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key])
            ? ($this->data[$key]['ttl'] !== null
                ? (time() - $this->data[$key]['time'] <= $this->data[$key]['ttl']
                    ? $this->data[$key]['value']
                    : $default
                )
                : $this->data[$key]['value']
            )
            : $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        $this->data[$key] = ['ttl' => $ttl, 'time' => time(), 'value' => $value];
        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        unset($this->data[$key]);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        $this->data = [];
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }
        return $values;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $time = time();
        foreach ($values as $key => $value) {
            $this->data[$key] = ['ttl' => $ttl, 'time' => $time, 'value' => $value];
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            unset($this->data[$key]);
        }
        return true;
    }

    public function __destruct()
    {
        $this->adapter->save($this->data);
    }
}
