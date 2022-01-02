<?php

namespace EasyTool\Framework\App\Cache;

use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    protected Adapter\AdapterInterface $adapter;
    protected array $data = [];
    protected string $name;
    protected bool $isEnabled;

    public function __construct(Adapter\AdapterInterface $adapter, string $name, bool $isEnabled)
    {
        $this->adapter = $adapter;
        $this->isEnabled = $isEnabled;
        $this->name = $name;

        $this->data = $this->adapter->load($this->name);
    }

    /**
     * Check whether the cache is enabled
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
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

    /**
     * Save data
     */
    public function save(): Cache
    {
        $this->adapter->save($this->name, $this->data);
        return $this;
    }

    /**
     * Save the data on destruct
     */
    public function __destruct()
    {
        $this->save();
    }
}
