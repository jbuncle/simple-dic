<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use SimpleDic\Util\FactoryStore;
use SimpleDic\Util\InstanceStore;
use SimpleDic\Util\TypeMapStore;
use SimpleDic\Util\TypeUtility;

/**
 * Container
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class Container implements ArgsInjector {

    /**
     *
     * @var InstanceStore
     */
    private $instanceStore;

    /**
     *
     * @var FactoryStore
     */
    private $factoryStore = [];

    /**
     *
     * @var TypeMapStore
     */
    private $typeMapStore;

    private function __construct() {
        $this->instanceStore = new InstanceStore();
        $this->factoryStore = new FactoryStore($this);
        $this->typeMapStore = new TypeMapStore();
    }

    public static function createContainer() {
        $container = new Container();
        $container->instanceStore->addInstance($container);
        return $container;
    }

    public function getInstance(string $class) {
        if (!TypeUtility::typeExists($class)) {
            throw new InvalidArgumentException("Class '$class' class does not exist");
        }
        $suitableInstance = $this->instanceStore->getSuitableInstance($class);
        if ($suitableInstance === null) {
            $instance = $this->createInstance($class);
            $this->instanceStore->addInstance($instance);
            return $instance;
        }
        return $suitableInstance;
    }

    /**
     * Make the container aware of given type, to use if needed.
     *
     * @param string $type
     */
    public function addType(string $type): void {
        // Add mapping to self
        $this->typeMapStore->addTypeMapping($type, $type);
    }

    public function addFactory(callable $method, string $class = '') {

        //Add to factory list
        $class = $this->factoryStore->add($method, $class);

        // Make container aware of type
        $this->addType($class);
    }

    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        $this->typeMapStore->addTypeMapping($for, $type, $overwrite);
    }

    private function createInstance(string $class) {
        // Check if given class is a mapped class
        $mappedClass = $this->typeMapStore->getSuitableMapping($class);
        // Ignore mapping to the same 
        if ($mappedClass !== null && $mappedClass !== $class) {
            return $this->createInstance($mappedClass);
        }

        // Search for an existing, compatible instance
        $object = $this->instanceStore->getSuitableInstance($class);
        if ($object !== null) {
            return $object;
        }

        // Look through type mappings for a suitable type
        $type = $this->typeMapStore->getSuitableMapping($class);
        if ($type !== null && $type !== $class) {
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
