<?php

namespace EasyTool\Framework\App\Database;

use DomainException;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Env\Config as EnvConfig;
use EasyTool\Framework\Validation\Validator;
use Laminas\Db\Adapter\Adapter;

class Manager
{
    public const ENV_PATH = 'database';
    public const DEFAULT_CONN = 'default';
    public const DRIVER_PDO_MYSQL = 'Pdo_Mysql';

    private DiContainer $diContainer;
    private EnvConfig $envConfig;
    private Validator $validator;

    private array $adapters = [];

    public function __construct(
        DiContainer $diContainer,
        EnvConfig $envConfig,
        Validator $validator
    ) {
        $this->diContainer = $diContainer;
        $this->envConfig = $envConfig;
        $this->validator = $validator;
    }

    /**
     * Check whether given config data is valid
     */
    private function validate(array $configData): bool
    {
        return $this->validator->validate(
            [
                'driver' => ['required', 'string', 'options' => [self::DRIVER_PDO_MYSQL]],
                'host' => ['required', 'string'],
                'database' => ['required', 'string'],
                'username' => ['required', 'string'],
                'password' => ['required', 'string'],
                'port' => ['int'],
                'charset' => ['string']
            ],
            $configData
        );
    }

    /**
     * Get a database adapter with specified name
     */
    public function getAdapter(string $name = self::DEFAULT_CONN): Adapter
    {
        if (!isset($this->adapters[$name])) {
            $configData = $this->envConfig->get(self::ENV_PATH);
            if (!isset($configData[$name])) {
                throw new DomainException(sprintf('Specified database adapter `%s` is not configured.', $name));
            }
            if (!$this->validate($configData[$name])) {
                throw new DomainException(sprintf('Invalid config for database adapter `%s`.', $name));
            }
            $this->adapters[$name] = $this->diContainer->create(
                Adapter::class,
                ['driver' => $configData[$name]]
            );
        }
        return $this->adapters[$name];
    }
}
