<?php

namespace EasyTool\Framework\App\Cache\Adapter;

interface AdapterInterface
{
    public function load();

    public function save();
}
