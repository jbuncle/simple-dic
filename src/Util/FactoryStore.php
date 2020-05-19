<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
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
 * FactoryStore
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class FactoryStore {

    /**
     *
     * @var \ArrayObject
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

    public function hasSuitableFactory(string $class): bool {
        return $this->getSuitableFactory($class) !== null;
    }

    /**
     * Find an instance which is either of the given type (class name), extends it,
     * or implements it.
     *
     * @param string $class
     *
     * @return callable|null
     */
    private function getSuitableFactory(string $class): ?callable {

        if ($this->factoryMethods->offsetExists($class)) {
            return $this->factoryMethods->offsetGet($class);
        }

        foreach ($this->factoryMethods as $factoryClass => $factory) {
            if (\is_subclass_of((string) $factoryClass, $class)) {
                return $factory;
            }
        }

        return null;
    }

    /**
     *
     * @param string $class
     *
     * @return mixed
     *
     * @throws ContainerException
     */
    public function createFromFactory(string $class) {
        $callable = $this->getSuitableFactory($class);

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
        } else if (is_string($callable) || $callable instanceof \Closure) {
            $reflectionFunction = new ReflectionFunction($callable);
            $params = $reflectionFunction->getParameters();
            $args = $this->getArgsForParams($params);
            $value = $reflectionFunction->invokeArgs($args);
        } else {
            throw new InvalidArgumentException("Unexpected callable variant " . var_export($callable, true));
        }

        if (!is_a($value, $class)) {
            throw new ContainerException("Factory for class '$class' returned value of incorrect type.");
        }

        return $value;
    }

    public function add(callable $method, string $class): string {

        if (empty($class)) {
            $reflection = $this->callableToReflection($method);
            $returnType = (string) $reflection->getReturnType();
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
        $this->factoryMethods->offsetSet($class, $method);

        return $class;
    }

    public function hasFactory(string $class): bool {
        return \array_key_exists($class, $this->factoryMethods);
    }

    /**
     *
     * @param array<\ReflectionParameter> $params
     *
     * @return array<mixed>
     */
    private function getArgsForParams(array $params): array {
        return $this->argsInjector->getArgsForParams($params);
    }

    private function callableToReflection(callable $callable): ReflectionFunctionAbstract {
        if (\is_array($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        } else if (is_string($callable) || $callable instanceof \Closure) {
            return new ReflectionFunction($callable);
        }

        throw new InvalidArgumentException("Unexpected callable variant " . var_export($callable, true));
    }

}
