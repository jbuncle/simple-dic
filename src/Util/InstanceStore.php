<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Util;

/**
 * InstanceStore
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class InstanceStore {

    private $instances;

    public function __construct() {
        $this->instances = new \ArrayObject();
    }

    public function addInstance($instance): void {
        $class = get_class($instance);
        $this->instances[$class] = $instance;
    }

    private function hasInstance(string $class): bool {
        return array_key_exists($class, $this->instances);
    }

    private function getInstance(string $class) {
        if ($this->hasInstance($class)) {
            return $this->instances[$class];
        }

        return null;
    }

    public function getSuitableInstance(string $class) {
        $instance = $this->getInstance($class);
        
        if ($instance !== null) {
            return $instance;
        }

        foreach ($this->instances as $instance) {
            if (is_a($instance, $class)) {
                // Add the instance so we can get it directly next time
                $this->addInstance($instance, $class);
                return $instance;
            }
        }

        return null;
    }

}
