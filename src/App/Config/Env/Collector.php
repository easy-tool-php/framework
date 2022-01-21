<?php

namespace EasyTool\Framework\App\Config\Env;

use EasyTool\Framework\App\Config;
use EasyTool\Framework\App\Config\AbstractCollector;

class Collector extends AbstractCollector
{
    protected string $namespace = Config::ENV;

    /**
     * Check the `app/config/env.php`
     */
    public function validate(array $config): bool
    {
        return $this->validator->validate(
            [
                'api.route'           => ['required', 'string'],
                'backend.route'       => ['required', 'string'],
                'cache.adapter'       => ['required', 'string'],
                'database'            => ['required', 'array'],
                'database.*.driver'   => ['required'],
                'database.*.host'     => ['string'],
                'database.*.database' => ['string'],
                'database.*.username' => ['string'],
                'database.*.password' => ['string']
            ],
            $config
        );
    }
}
