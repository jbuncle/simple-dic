<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 CyberPear (https://www.cyberpear.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

use ReflectionClass;

/**
 * ContainerUtil
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ContainerUtil {

    /**
     *
     * @param Container $container
     * @param array<callable> $factories
     *
     * @return void
     */
    public static function addFactories(Container $container, array $factories): void {
        foreach ($factories as $type => $factory) {
            $container->addFactory($factory, strval($type));
        }
    }

    public static function addFactoriesFromClass(Container $container, string $class): void {

        $reflectionClass = new ReflectionClass($class);
        /* @var $method \ReflectionMethod */
        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->isStatic()) {
                $container->addFactory([$class, $method->getName()]);
            }
        }
    }

}
