<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

use ArrayObject;
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
class Container implements ArgsInjector {

    private $instanceStore;

    /**
     *
     * @var callable
     */
    private $factoryStore = [];

    /**
     *
     * @var string[]
     */
    private $typeStore;
    private $typeMapStore;

    private function __construct() {
        $this->instanceStore = new Util\InstanceStore();
        $this->factoryStore = new Util\FactoryStore($this);
        $this->typeStore = new Util\TypeStore();
        $this->typeMapStore = new Util\TypeMapStore();
    }

    public static function createContainer() {
        $container = new Container();
        $container->instanceStore->addInstance($container);
        return $container;
    }

    public function getInstance(string $class) {
        if (!$this->typeExists($class)) {
            throw new InvalidArgumentException("Class '$class' class does not exist");
        }
        if (!$this->instanceStore->hasInstance($class)) {
            $this->instanceStore->addInstance($this->createInstance($class));
        }
        return $this->instanceStore->getSuitableInstance($class);
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
     * Make the container aware of given type, to use if needed.
     *
     * @param string $type
     */
    public function addType(string $type): void {
        $this->typeStore->addType($type);
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
        $this->factoryStore->add($class, $method);

        // Make container aware of type
        $this->addType($class);
    }

    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        $this->typeMapStore->addTypeMapping($for, $type, $overwrite);
    }

    private function createInstance(string $class) {
        // Check if given class is a mapped class
        if ($this->typeMapStore->hasMapping($class)) {
            $mappedClass = $this->typeMapStore->getSuitableMapping($class);

            // Ignore mapping to the same 
            if ($mappedClass !== $class) {
                return $this->createInstance($mappedClass);
            }
        }

        // Search for an existing, compatible instance
        $object = $this->instanceStore->getSuitableInstance($class);
        if ($object !== null) {
            return $object;
        }
        // Search for compatible defined type
        $type = $this->typeStore->getSuitableType($class);
        if ($type !== null) {
            return $this->createInstance($type);
        }

        // Look through type mappings for a suitable type
        $type = $this->typeMapStore->getSuitableMapping($class);
        if ($type !== null) {
            return $this->createInstance($type);
        }

        return $this->createNewInstance($class);
    }

    private function createNewInstance(string $class) {

        if ($this->factoryStore->hasFactory($class)) {
            return $this->factoryStore->createFromFactory($class);
        } else {
            return $this->autoCreateInstance($class);
        }
    }

    private function callableToReflection(callable $callable): ReflectionFunctionAbstract {
        if (is_array($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        } else {
            return new ReflectionFunction($callable);
        }
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
    public function getArgsForParams(array $params): array {
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
