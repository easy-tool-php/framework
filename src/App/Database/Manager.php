<?php

namespace EasyTool\Framework\App\Database;

use EasyTool\Framework\App\Config\Manager as ConfigManager;
use EasyTool\Framework\App\Exception\DatabaseException;
use EasyTool\Framework\App\ObjectManager;
use EasyTool\Framework\Validation\Validator;
use Laminas\Db\Adapter\Adapter;

class Manager
{
    public const CONFIG_NAME = 'database';

    public const DB_DRIVER = 'driver';
    public const DB_HOST = 'host';
    public const DB_DATABASE = 'database';
    public const DB_USERNAME = 'username';
    public const DB_PASSWORD = 'password';

    public const DRIVER_PDO_MYSQL = 'Pdo_Mysql';

    private ConfigManager $configManager;
    private ObjectManager $objectManager;
    private Validator $validator;

    private array $adapters = [];

    public function __construct(
        ConfigManager $configManager,
        ObjectManager $objectManager,
        Validator $validator
    ) {
        $this->configManager = $configManager;
        $this->objectManager = $objectManager;
        $this->validator = $validator;
    }

    /**
     * Collect config data from `app/config/database.php` and initialize adapters
     */
    public function initialize()
    {
        $adapterConfigs = $this->configManager->getConfig(self::CONFIG_NAME)->getData();

        foreach ($adapterConfigs as $name => $config) {
            if (!$this->validator->validate(
                [
                    self::DB_DRIVER => ['required', 'string', 'options' => [self::DRIVER_PDO_MYSQL]],
                    self::DB_HOST => ['required', 'string'],
                    self::DB_DATABASE => ['required', 'string'],
                    self::DB_USERNAME => ['required', 'string'],
                    self::DB_PASSWORD => ['required', 'string']
                ],
                $config
            )) {
                throw new DatabaseException('Invalid database config.');
            }

            /** @var Adapter $adapter */
            $this->adapters[$name] = $this->objectManager->create(Adapter::class, ['driver' => $config]);
        }
    }

    /**
     * Get a database adapter with specified name
     */
    public function getAdapter($name = 'default')
    {
        return $this->adapters[$name];
    }
}
