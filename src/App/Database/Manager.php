<?php

namespace EasyTool\Framework\App\Database;

use DomainException;
use EasyTool\Framework\App\Di\Container as DiContainer;
use EasyTool\Framework\App\Env\Config as EnvConfig;
use EasyTool\Framework\App\Exception\ConfigException;
use EasyTool\Framework\Validation\Validator;
use Laminas\Db\Adapter\Adapter;

class Manager
{
    public const CONFIG_PATH = 'database';

    public const DEFAULT_CONN = 'default';

    public const DB_DRIVER = 'driver';
    public const DB_HOST = 'host';
    public const DB_DATABASE = 'database';
    public const DB_USERNAME = 'username';
    public const DB_PASSWORD = 'password';

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
     * Collect config data from `app/config/database.php` and initialize adapters
     */
    public function initialize()
    {
        $adapterConfigs = $this->envConfig->get(self::CONFIG_PATH);

        foreach ($adapterConfigs as $name => $config) {
            if (
                !$this->validator->validate(
                    [
                        self::DB_DRIVER   => ['required', 'string', 'options' => [self::DRIVER_PDO_MYSQL]],
                        self::DB_HOST     => ['required', 'string'],
                        self::DB_DATABASE => ['required', 'string'],
                        self::DB_USERNAME => ['required', 'string'],
                        self::DB_PASSWORD => ['required', 'string']
                    ],
                    $config
                )
            ) {
                throw new ConfigException('Invalid database config.');
            }

            /** @var Adapter $adapter */
            $this->adapters[$name] = $this->diContainer->create(Adapter::class, ['driver' => $config]);
        }
    }

    /**
     * Get a database adapter with specified name
     */
    public function getAdapter(string $name = self::DEFAULT_CONN): Adapter
    {
        if (!isset($this->adapters[$name])) {
            throw new DomainException(sprintf('Unexpected adapter: %s', $name));
        }
        return $this->adapters[$name];
    }
}
