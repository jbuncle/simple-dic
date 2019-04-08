<?php

/*
 * Copyright (C) 2019 CyberPear (https://www.cyberpear.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

/**
 * ContainerUtil
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ContainerUtil {

    public static function addFactories(Container $container, array $factories) {
        foreach ($factories as $type => $factory) {
            $container->addFactory($factory, $type);
        }
    }

    public static function addFactoriesFromClass(Container $container, string $class) {

        $reflectionClass = new \ReflectionClass($class);
        /* @var $method \ReflectionMethod */
        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->isStatic()) {
                $container->addFactory([$class, $method->getName()]);
            }
        }
    }

}
