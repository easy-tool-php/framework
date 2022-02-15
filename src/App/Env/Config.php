<?php
/**
 * Copyright (c) Zengliwei 2022. All rights reserved.
 * See LICENSE for license details.
 */

namespace EasyTool\Framework\App\Env;

use EasyTool\Framework\App\Config\AbstractFileConfig;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\Validation\Validator;

class Config extends AbstractFileConfig
{
    protected string $filename = 'env.php';
    protected array $format = [
        'api.route'     => ['required', 'string'],
        'backend.route' => ['required', 'string'],
        'cache.adapter' => ['required', 'string'],
        'cache.options' => ['array'],
        'database'      => ['required', 'array']
    ];

    public function __construct(
        Directory $directory,
        Validator $validator,
        array $data = []
    ) {
        parent::__construct($validator, $data);
        $this->data = $this->collectData($directory->getDirectoryPath(Directory::CONFIG));
    }
}
