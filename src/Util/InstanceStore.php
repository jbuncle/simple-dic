<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Util;

/**
 * InstanceStore
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class InstanceStore {

    /**
     * Map of instances.
     * @var ArrayObject
     */
    private $instances;

    public function __construct() {
        $this->instances = new \ArrayObject();
    }

    /**
     *
     * @param string $class
     * @param mixed $instance
     *
     * @return void
     */
    public function addInstance(string $class, $instance): void {
        $this->instances[$class] = $instance;
    }

    private function hasInstance(string $class): bool {
        return array_key_exists($class, $this->instances);
    }

    /**
     *
     * @param string $class
     *
     * @return mixed
     */
    private function getInstance(string $class) {
        if ($this->hasInstance($class)) {
            return $this->instances[$class];
        }

        return null;
    }

    /**
     * Find an instance which is either of the given type (class name), extends it,
     * or implements it.
     *
     * @param string $class
     * @return type
     */
    public function getSuitableInstance(string $class): type {
        $instance = $this->getInstance($class);

        if ($instance !== null) {
            return $instance;
        }

        foreach ($this->instances as $instance) {
            if (is_a($instance, $class)) {
                // Add the instance so we can get it directly next time
                $this->addInstance($class, $instance);
                return $instance;
            }
        }

        return null;
    }

}
