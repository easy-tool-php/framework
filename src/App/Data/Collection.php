<?php

namespace EasyTool\Framework\App\Data;

use ArrayIterator;

class Collection implements \IteratorAggregate
{
    protected array $items = [];

    public function count(): int
    {
        return count($this->items);
    }

    public function getItemById($id): ?DataObject
    {
        return $this->items[$id] ?? null;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this->items as $item) {
            $array[] = $item->toArray();
        }
        return $array;
    }
}
