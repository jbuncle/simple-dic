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

    public function addInstance($instance) {
        $class = get_class($instance);
        $this->instances[$class] = $instance;
    }

    public function hasInstance(string $class) {
        return array_key_exists($class, $this->instances);
    }

    private function getInstance(string $class) {
        return $this->instances[$class];
    }

    public function getSuitableInstance(string $class) {
        if ($this->hasInstance($class)) {
            return $this->getInstance($class);
        }
        foreach ($this->instances as $instance) {
            if (is_a($instance, $class)) {
                $this->addInstance($instance, $class);
                return $instance;
            }
        }

        return null;
    }

}
