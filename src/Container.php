<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Container
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class Container {

    private $instances = [];

    /**
     *
     * @var callable
     */
    private $factoryMethods = [];

    /**
     *
     * @var string[]
     */
    private $types = [];
    private $typeMap = [];

    private function __construct() {
        // Add self
        $this->types[get_class($this)] = $this;
    }

    public static function createContainer() {
        $container = new Container();
        $container->instances[get_class($container)] = $container;
        return $container;
    }

    public function getInstance(string $class) {
        if (!$this->typeExists($class)) {
            throw new InvalidArgumentException("Class '$class' class does not exist");
        }
        if (!array_key_exists($class, $this->instances)) {
            $this->instances[$class] = $this->createInstance($class);
        }
        return $this->instances[$class];
    }

    /**
     * Check if the given type actually exists.
     *
     * @param string $type The class or interface name (inc. namespace)
     *
     * @return bool
     */
    private function typeExists(string $type): bool {
        return interface_exists($type) || class_exists($type);
    }

    /**
     * Tell the container to use a specific type when another is requested.
     *
     * @param string $for The type we want to map
     * @param string $type The type to use
     */
    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        if (!$this->typeExists($for)) {
            throw new InvalidArgumentException("For '$for' class does not exist");
        }
        if (!$this->typeExists($type)) {
            throw new InvalidArgumentException("Type '$type' class does not exist");
        }

        if ($overwrite || !array_key_exists($for, $this->typeMap)) {
            $this->typeMap[$for] = $type;
        }
    }

    /**
     * Make the container aware of given type, to use if needed.
     *
     * @param string $type
     */
    public function addType(string $type): void {
        if (!$this->typeExists($type)) {
            throw new InvalidArgumentException("Type '$type' class does not exist");
        }
        $this->types[] = $type;
    }

    public function addFactory(callable $method, string $class = '') {

        if (empty($class)) {
            $reflection = $this->callableToReflection($method);
            $returnType = $reflection->getReturnType();
            $methodName = $reflection->getName();
            if ($returnType === null) {
                throw new InvalidArgumentException("Can't establish type for factory '$methodName'");
            }
            if (!$this->typeExists($returnType)) {
                throw new InvalidArgumentException("Return type '$returnType' is not a class");
            }
            $class = (string) $returnType;
        }

        //Add to factory list
        $this->factoryMethods[$class] = $method;

        // Make container aware of type
        $this->addType($class);
    }

    private function createInstance(string $class) {
        // Check if given class is a mapped class
        if (\array_key_exists($class, $this->typeMap)) {
            $mappedClass = $this->typeMap[$class];

            // Ignore mapping to the same 
            if ($mappedClass !== $class) {
                return $this->createInstance($mappedClass);
            }
        }

        // Search for an existing, compatible instance
        $object = $this->findSuitableObject($this->instances, $class);
        if ($object !== null) {
            return $object;
        }
        // Search for compatible defined type
        $type = $this->findSuitableType($this->types, $class);
        if ($type !== null) {
            return $this->createInstance($type);
        }

        // Look through type mappings for a suitable type
        $type = $this->findSuitableType(array_keys($this->typeMap), $class);
        if ($type !== null) {
            return $this->createInstance($type);
        }


        return $this->createNewInstance($class);
    }

    private function findSuitableObject(array $objects, string $class): ?object {
        foreach ($objects as $object) {
            if (is_a($object, $class)) {
                return $object;
            }
        }

        return null;
    }

    private function findSuitableType(array $types, string $class): ?string {
        foreach ($types as $type) {
            if (is_subclass_of($type, $class)) {
                return $type;
            }
        }

        return null;
    }

    private function createNewInstance(string $class) {

        if ($this->hasFactory($class)) {
            return $this->createFromFactory($class);
        } else {
            return $this->autoCreateInstance($class);
        }
    }

    private function createFromFactory(string $class) {
        $callable = $this->factoryMethods[$class];

        // TODO: check callback return type
        if (is_array($callable)) {
            if (is_string($callable[0])) {
                // Handle static
                $reflectionMethod = new ReflectionMethod($callable[0], $callable[1]);
                $params = $reflectionMethod->getParameters();
                $args = $this->getArgsForParams($params);

                $value = $reflectionMethod->invokeArgs(null, $args);
            } else {
                // Handle non-static
                $reflectionMethod = new ReflectionMethod(get_class($callable[0]), $callable[1]);
                $params = $reflectionMethod->getParameters();
                $args = $this->getArgsForParams($params);

                $value = $reflectionMethod->invokeArgs($callable[0], $args);
            }
        } else {
            $reflectionFunction = new ReflectionFunction($callable);
            $params = $reflectionFunction->getParameters();
            $args = $this->getArgsForParams($params);
            $value = $reflectionFunction->invokeArgs($args);
        }
        if (!is_a($value, $class)) {
            throw new ContainerException("Factory for class '$class' returned value of incorrect type.");
        }
        return $value;
    }

    private function callableToReflection(callable $callable): ReflectionFunctionAbstract {
        if (is_array($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        } else {
            return new ReflectionFunction($callable);
        }
    }

    private function hasFactory(string $class): bool {
        return \array_key_exists($class, $this->factoryMethods);
    }

    private function autoCreateInstance(string $class) {
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
     * 
     * @param ReflectionParameter[] $params
     */
    private function getArgsForParams(array $params): array {
        // TODO: add support to ignore scalar typed, defaulted arguments
        $args = [];
        foreach ($params as $param) {
            if (!$param->hasType()) {
                if ($param->isOptional()) {
                    // End of args
                    break;
                }
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
