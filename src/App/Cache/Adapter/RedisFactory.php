<?php

namespace EasyTool\Framework\App\Cache\Adapter;

use DomainException;
use EasyTool\Framework\Validation\Validator;
use Laminas\Cache\Storage\Adapter\AbstractAdapter;
use Laminas\Cache\Storage\Adapter\Redis;

class RedisFactory implements FactoryInterface
{
    private Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritDoc
     */
    public function create(array $options): AbstractAdapter
    {
        if (
            !$this->validator->validate(
                [
                    'server'              => ['required'],
                    'server.host'         => ['required', 'string'],
                    'server.port'         => ['int'],
                    'server.timeout'      => ['int'],
                    'database'            => ['int'],
                    'lib_options'         => ['array'],
                    'namespace_separator' => ['string'],
                    'password'            => ['string'],
                    'persistent_id'       => ['string'],
                    'resource_manager'    => ['string']
                ],
                $options
            )
        ) {
            throw new DomainException('Invalid cache options.');
        }
        return new Redis($options);
    }
}
