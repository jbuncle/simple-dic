<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace JBuncle\SimpleDic\Util;

use ArrayObject;

/**
 * InstanceStore
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class InstanceStore {

    /**
     * Map of instances.
     *
     * @var ArrayObject
     */
    private $instances;

    public function __construct() {
        $this->instances = new ArrayObject();
    }

    /**
     *
     * @param string $class
     * @param mixed $instance
     *
     * @return void
     */
    public function addInstance(string $class, $instance): void {
        $this->instances->offsetSet($class, $instance);
    }

    /**
     * Find an instance which is either of the given type (class name), extends it,
     * or implements it.
     *
     * @param string $class
     * @return mixed
     */
    public function getSuitableInstance(string $class) {
        // Lookupfrom instance cache
        if ($this->instances->offsetExists($class)) {
            return $this->instances->offsetGet($class);
        }

        foreach ($this->instances as $instance) {
            if (is_a($instance, $class)) {
                // Add the instance so we can get it directly next time
                $this->addInstance($class, $instance);
                return $instance;
            }
        }

        // Add null to instance cache so we don't try again
        $this->addInstance($class, null);
        return null;
    }

}
