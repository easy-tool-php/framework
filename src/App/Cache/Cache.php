<?php

namespace EasyTool\Framework\App\Cache;

use EasyTool\Framework\App\Cache\Exception\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

class Cache implements CacheInterface
{
    protected Adapter\AdapterInterface $adapter;
    protected array $data = [];
    protected string $name;
    protected bool $isEnabled;
    protected bool $saveOnDestruct;

    public function __construct(
        Adapter\AdapterInterface $adapter,
        string $name,
        bool $isEnabled,
        bool $saveOnDestruct = true
    ) {
        $this->adapter = $adapter;
        $this->isEnabled = $isEnabled;
        $this->name = $name;
        $this->saveOnDestruct = $saveOnDestruct;
        $this->data = $this->adapter->load($this->name);
    }

    /**
     * Check whether the cache key is valid.
     */
    protected function checkKey($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Invalid cache key.');
        }
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
        $this->checkKey($key);
        return isset($this->data[$key]);
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $this->checkKey($key);
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
        $this->checkKey($key);
        $this->data[$key] = ['ttl' => $ttl, 'time' => time(), 'value' => $value];
        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete($key): bool
    {
        $this->checkKey($key);
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
            $this->checkKey($key);
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
    public function save(): self
    {
        $this->adapter->save($this->name, $this->data);
        return $this;
    }

    /**
     * Save the data on destruct
     */
    public function __destruct()
    {
        if ($this->saveOnDestruct) {
            $this->save();
        }
    }
}
