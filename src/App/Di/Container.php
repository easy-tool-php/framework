<?php

namespace EasyTool\Framework\App\Di;

use Laminas\Di\Config;
use Laminas\Di\DefaultContainer;
use Laminas\Di\Injector;

class Container extends DefaultContainer
{
    private static ?self $instance = null;

    /**
     * Create a new instance of a class or alias
     */
    public function create(string $name, array $parameters = []): ?object
    {
        return $this->injector->create($name, $parameters);
    }

    /**
     * Append dependency injection configuration
     */
    public function appendConfig($diConfig): self
    {
        /** @var Config $config */
        $config = $this->get(Config::class);
        if (!empty($diConfig['preferences'])) {
            foreach ($diConfig['preferences'] as $alias => $class) {
                $config->setAlias($alias, $class);
            }
        }
        if (!empty($diConfig['types'])) {
            foreach ($diConfig['types'] as $type => $typeConfig) {
                if (!empty($typeConfig['preferences'])) {
                    foreach ($typeConfig['preferences'] as $preference) {
                        $config->setTypePreference($type, $preference);
                    }
                }
                if (!empty($typeConfig['parameters'])) {
                    $config->setParameters($type, $typeConfig['parameters']);
                }
            }
        }
        return $this;
    }

    /**
     * Get the container singleton
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $config = new Config();
            $injector = new Injector($config);
            self::$instance = (new Container($injector))->setInstance(Config::class, $config);
            $injector->setContainer(self::$instance);
        }
        return self::$instance;
    }
}
