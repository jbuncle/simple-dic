<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

use Exception;
use ReflectionClass;
use ReflectionFunction;
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
            throw new \InvalidArgumentException("Class '$class' class does not exist");
        }
        if (!array_key_exists($class, $this->instances)) {
            $this->instances[$class] = $this->createInstance($class);
        }
        return $this->instances[$class];
    }
    
    private function typeExists(string $type) {
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
            throw new \InvalidArgumentException("For '$for' class does not exist");
        }
        if (!$this->typeExists($type)) {
            throw new \InvalidArgumentException("Type '$type' class does not exist");
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
            throw new \InvalidArgumentException("Type '$type' class does not exist");
        }
        $this->types[] = $type;
    }

    public function addFactory(callable $method, string $class = '') {

        if (empty($class)) {
            $reflection = $this->callableToReflection($method);
            $returnType = $reflection->getReturnType();
            $methodName = $reflection->getName();
            if ($returnType === null) {
                throw new \InvalidArgumentException("Can't establish type for factory '$methodName'");
            }
            if (!$this->typeExists($returnType)) {
                throw new \InvalidArgumentException("Return type '$returnType' is not a class");
            }
            $class = (string) $returnType;
        }

        // Make container aware of type
        $this->addType($class);

        //Add to factory list
        $this->factoryMethods[$class] = $method;
    }

    private function createInstance(string $class) {
        if (\array_key_exists($class, $this->typeMap)) {
            $mappedClass = $this->typeMap[$class];

            // Ignore mapping to the same type
            if ($mappedClass !== $class) {
                return $this->createInstance($mappedClass);
            }
        }

        // Search for an existing, compatible instance
        foreach ($this->instances as $instance) {
            if (\is_a($instance, $class)) {
                return $instance;
            }
        }
        // Search for compatible type
        foreach ($this->types as $type) {
            if (is_subclass_of($type, $class)) {
                return $this->createInstance($type);
            }
        }
        // Look through type mappings for a suitable type
        foreach (array_keys($this->typeMap) as $type) {
            if (is_subclass_of($type, $class)) {
                return $this->createInstance($type);
            }
        }

        return $this->createNewInstance($class);
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
            $reflectionMethod = new ReflectionMethod($callable[0], $callable[1]);
            $params = $reflectionMethod->getParameters();
            $args = $this->getArgsForParams($params);

            // TODO: support for non-static factory methods
            $value = $reflectionMethod->invokeArgs(null, $args);
        } else {
            $reflectionFunction = new ReflectionFunction($callable);
            $params = $reflectionFunction->getParameters();
            $args = $this->getArgsForParams($params);
            $value = $reflectionFunction->invokeArgs($args);
        }
        if (!is_a($value, $class)) {
            throw new Exception("Factory for class '$class' returned value of incorrect type.");
        }
        return $value;
    }

    private function callableToReflection(callable $callable): \ReflectionFunctionAbstract {
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
                throw new Exception("Can't auto inject param {$param->getName()}");
            }
            $paramType = $param->getClass();
            if (!$paramType) {
                $paramName = $param->getName();
                throw new Exception("Missing type for param '$paramName'");
            }
            $args[] = $this->getInstance($paramType->getName());
        }
        return $args;
    }

}
