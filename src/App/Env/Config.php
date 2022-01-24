<?php

namespace EasyTool\Framework\App\Env;

use DomainException;
use EasyTool\Framework\App\Config\AbstractConfig;
use EasyTool\Framework\App\Filesystem\Directory;
use EasyTool\Framework\Validation\Validator;

class Config extends AbstractConfig
{
    public const FILENAME = 'env.php';

    public function __construct(
        Directory $directory,
        Validator $validator
    ) {
        $data = require $directory->getDirectoryPath(Directory::CONFIG) . '/' . self::FILENAME;
        if (!$validator->validate(
            [
                'api.route'     => ['required', 'string'],
                'backend.route' => ['required', 'string'],
                'cache.adapter' => ['required', 'string'],
                'cache.options' => ['array'],
                'database'      => ['required', 'array']
            ],
            $data
        )) {
            throw new DomainException('Invalid environment config data.');
        }
        parent::__construct($data);
    }
}
