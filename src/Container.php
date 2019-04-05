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

    private $typeMap = [];

    public function getInstance(string $class) {
        // TODO: add support for interfaces
        if (!array_key_exists($class, $this->instances)) {
            $this->instances[$class] = $this->createInstance($class);
        }
        return $this->instances[$class];
    }
    
    /**
     * Tell the container to use a specific type when another is requested.
     *
     * @param string $for The type we want to map
     * @param string $type The type to use
     */
    public function addTypeMapping($for, $type) {
        $this->typeMap[$for] = $type;
    }
    
    public function addFactory(string $class, callable $method) {
        $this->factoryMethods[$class] = $method;
    }

    private function createInstance(string $class) {
        if (array_key_exists($class, $this->typeMap)) {
            $class = $this->typeMap[$class];
        }
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
            throw new Exception("Factory for class '$class'.");
        }
        return $value;
    }

    private function hasFactory(string $class): bool {
        return array_key_exists($class, $this->factoryMethods);
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
