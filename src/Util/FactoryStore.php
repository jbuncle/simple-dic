<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Util;

use ArrayObject;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use SimpleDic\ArgsInjector;
use SimpleDic\ContainerException;

/**
 * Description of FactoryStore
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class FactoryStore {

    /**
     *
     * @var callable
     */
    private $factoryMethods;

    /**
     *
     * @var ArgsInjector
     */
    private $argsInjector;

    public function __construct(ArgsInjector $argsInjector) {
        $this->argsInjector = $argsInjector;
        $this->factoryMethods = new ArrayObject();
    }

    public function createFromFactory(string $class) {
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

    public function get(string $class): callable {
        return $this->factoryStore[$class];
    }

    public function add(callable $method, string $class): string {

        if (empty($class)) {
            $reflection = $this->callableToReflection($method);
            $returnType = $reflection->getReturnType();
            $methodName = $reflection->getName();
            if ($returnType === null) {
                throw new InvalidArgumentException("Can't establish type for factory '$methodName'");
            }
            if (!TypeUtility::typeExists($returnType)) {
                throw new InvalidArgumentException("Return type '$returnType' is not a class");
            }
            $class = (string) $returnType;
        }

        //Add to factory list
        $this->factoryMethods[$class] = $method;

        return $class;
    }

    public function hasFactory(string $class): bool {
        return \array_key_exists($class, $this->factoryMethods);
    }

    private function getArgsForParams(array $params): array {
        return $this->argsInjector->getArgsForParams($params);
    }

    private function callableToReflection(callable $callable): ReflectionFunctionAbstract {
        if (TypeUtility::isCallableAMethod($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        } else {
            return new ReflectionFunction($callable);
        }
    }

}
