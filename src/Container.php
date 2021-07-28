<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use SimpleDic\Util\FactoryStore;
use SimpleDic\Util\InstanceStore;
use SimpleDic\Util\TypeMapStore;
use Throwable;

/**
 * Container
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class Container implements ArgsInjector, ContainerInterface, ContainerSetupInterface {

    /**
     *
     * @var InstanceStore
     */
    private $instanceStore;

    /**
     *
     * @var FactoryStore
     */
    private $factoryStore;

    /**
     *
     * @var TypeMapStore
     */
    private $typeMapStore;

    /**
     *
     * @var array<string>
     */
    private $queue;

    private function __construct() {
        $this->instanceStore = new InstanceStore();
        $this->factoryStore = new FactoryStore($this);
        $this->typeMapStore = new TypeMapStore();
        $this->queue = [];
    }

    public static function createContainer(): self {
        $container = new self();
        // Add the container itself.
        $container->instanceStore->addInstance(get_class($container), $container);
        return $container;
    }

    /**
     * Get an instance of the given class name.
     *
     * @param string $class The class name.
     * @return mixed
     * @throws InvalidArgumentException If the class name is for a non-existing type
     */
    public function getInstance(string $class) {
        // Attempt to find an existing suitable instance
        $suitableInstance = $this->instanceStore->getSuitableInstance($class);
        if ($suitableInstance !== null) {
            return $suitableInstance;
        }

        // Create a new instance
        $instance = $this->createInstance($class);

        // Store the instance for next time the class is requested
        $this->instanceStore->addInstance($class, $instance);

        return $instance;
    }

    public function hasInstance(string $class): bool {
        return $this->getInstance($class) !== null;
    }

    /**
     * Make the container aware of given type to use if needed.
     *
     * This allows a quick and easy way to make the container aware of an instance
     * to use. For example, when an interface is used you can declare the implementing
     * class.
     *
     * @deprecated Use addTypeMapping to be explicit of the types
     *
     * @param string $type
     */
    public function addType(string $type): void {
        // Add mapping to self
        $this->addTypeMapping($type, $type);
    }

    /**
     * Add a factory method for the container to use when looking up a type.
     *
     * The factory method's (callback's) parameters will be autowired.
     *
     * If 'class' isn't defined, the return type will be looked up.
     *
     * @param callable $method The callback.
     * @param string   $class  The return type (the type the callback provides).
     */
    public function addFactory(callable $method, string $class = ''): void {
        // Add to factory store
        $class = $this->factoryStore->add($method, $class);
        // Make container aware of type
        $this->addTypeMapping($class, $class);
    }

    /**
     * Tell the container to use the class defined in 'type', when the class 'for'
     * is defined.
     *
     * @param string $for     The class to map/alias
     * @param string $type    The type to use
     * @param bool $overwrite Whether to overwrite an existing mapping for 'for'
     *
     * @return void
     */
    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        $this->typeMapStore->addTypeMapping($for, $type, $overwrite);
    }

    /**
     *
     * @param string $class
     *
     * @return mixed
     */
    private function createInstance(string $class) {
        if (in_array($class, $this->queue)) {
            // Already doing
            throw new \Exception("Recursion protection '" . implode("' => '", $this->queue) . "' => '$class'");
        }
        array_push($this->queue, $class);
        try {
            $instance = $this->doCreateInstance($class);
        } catch(Throwable $ex){
            throw new ContainerException("Failed to create instance of '$class' due to '{$ex->getMessage()}'", 0, $ex);  
        } finally {
            $popped = array_pop($this->queue);
        }
        if ($popped !== $class) {
            // This shouldn't actaully ever happen.
            throw new ContainerException("Mismatched instance '$class' => '$popped'");
        }
        return $instance;
    }

    private function doCreateInstance(string $class) {

        // Check if given class is a mapped class
        $mappedClass = $this->typeMapStore->getSuitableMapping($class);

        // Ignore mapping to the same (prevent infinite recursion)
        if ($mappedClass !== null && $mappedClass !== $class) {
            return $this->createInstance($mappedClass);
        }

        // Search for an existing, compatible instance
        $object = $this->instanceStore->getSuitableInstance($class);
        if ($object !== null) {
            // Found existing instance
            return $object;
        }

        // Look through type mappings for a suitable type
        $type = $this->typeMapStore->getSuitableMapping($class);
        if ($type !== null && $type !== $class) {
            // Create instance of mapped type
            return $this->createInstance($type);
        }
        return $this->createNewInstance($class);
    }

    /**
     *
     * @param string $class
     * @return mixed
     */
    private function createNewInstance(string $class) {

        if ($this->factoryStore->hasFactory($class)) {
            return $this->factoryStore->createFromFactory($class);
        } else {
            return $this->autowireInstance($class);
        }
    }

    /**
     * Create instance of given class.
     *
     * @param string $class
     *
     * @return mixed
     *
     * @throws ContainerException
     */
    private function autowireInstance(string $class) {
        $reflection = new ReflectionClass($class);

        if ($reflection->isInterface()) {
            throw new ContainerException("Cannot create instance from interface '$class'");
        }

        /* @var $constructor ReflectionMethod */
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstanceArgs([]);
        }

        /* @var $constructorParams ReflectionParameter[] */
        $constructorParams = $constructor->getParameters();

        $args = $this->getArgsForParams($constructorParams);

        return $reflection->newInstanceArgs($args);
    }

    /**
     * Get instances for given array of ReflectionParameters.
     *
     * @param array<ReflectionParameter> $params
     *
     * @return array<mixed>
     *
     * @throws ContainerException
     */
    public function getArgsForParams(array $params): array {
        // TODO: add support to ignore scalar typed, defaulted arguments
        $args = [];
        foreach ($params as $param) {
            if (!$param->hasType()) {
                if ($param->isOptional()) {
                    // No type, but optional (allowed but treated as end of arguments)
                    break;
                }

                // Non-optional and can't create instance
                throw new ContainerException("Can't auto inject param {$param->getName()}");
            }

            $paramType = $param->getClass();
            if (!$paramType) {
                // Handle defaults
                if ($param->isOptional()) {
                    // End of args
                    break;
                }

                $paramName = $param->getName();
                throw new ContainerException("Missing type for param '$paramName'");
            }

            try {
                $args[] = $this->getInstance($paramType->getName());
            } catch (ContainerException $ex) {
                if ($param->isOptional()) {
                    // End of args
                    break;
                }

                throw $ex;
            }
        }

        return $args;
    }

}
