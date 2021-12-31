<?php

namespace EasyTool\Framework\App;

use Exception;
use ReflectionException;
use ReflectionParameter;

class ObjectManager
{
    private static ?ObjectManager $instance = null;

    /**
     * Class aliases mapping, format is like ['alias' => 'class_name']
     */
    private array $classAliases = [];

    /**
     * Singleton aliases mapping, format is like ['alias' => $singleton]
     */
    private array $singletons = [];

    /**
     * Get object manager singleton
     */
    public static function getInstance(): ObjectManager
    {
        if (self::$instance === null) {
            self::$instance = new ObjectManager();
        }
        return self::$instance;
    }

    /**
     * Collect class aliases to build a mapping,
     *     system follows the mapping to get singletons or create instances
     *
     * @param array $classAliases Class aliases mapping, format is like ['alias' => 'class_name']
     */
    public function collectClassAliases(array $classAliases): void
    {
        $this->classAliases = array_merge(
            $this->classAliases,
            array_map(
                function ($classAlias) {
                    return trim($classAlias, '\\');
                },
                $classAliases
            )
        );
    }

    /**
     * Create an instance with specified alias
     *
     * @param array $argumentArr Argument array, format is like ['argument_name' => $value]
     * @throws ReflectionException
     * @throws Exception
     */
    public function create(string $classAlias, array $argumentArr = []): object
    {
        $classAlias = trim($classAlias, '\\');

        $reflectionClass = new \ReflectionClass('\\' . $classAlias);
        if (!($constructor = $reflectionClass->getConstructor())) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            /* @var $parameter ReflectionParameter */
            if (isset($argumentArr[$parameter->getName()])) {
                $arguments[] = $argumentArr[$parameter->getName()];
            } elseif ($parameter->isOptional()) {
                $arguments[] = $parameter->getDefaultValue();
            } elseif (($injectedClass = $parameter->getType())) {
                $arguments[] = $this->get($injectedClass->getName());
            } else {
                throw new Exception(
                    sprintf('Argument `%s` of class `%s` is required.', $parameter->getName(), $classAlias)
                );
            }
        }
        return $reflectionClass->newInstanceArgs($arguments);
    }

    /**
     * Get a singleton with specified alias, create one when it does not exist.
     *
     * @throws ReflectionException
     */
    public function get(string $classAlias, array $argumentArr = []): object
    {
        $classAlias = trim($classAlias, '\\');
        if ($classAlias == self::class) {
            return self::getInstance();
        }
        if (isset($this->classAliases[$classAlias])) {
            return $this->get($this->classAliases[$classAlias], $argumentArr);
        }
        if (!isset($this->singletons[$classAlias])) {
            $this->singletons[$classAlias] = $this->create($classAlias, $argumentArr);
        }
        return $this->singletons[$classAlias];
    }
}