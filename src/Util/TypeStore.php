<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Util;

use ArrayObject;
use InvalidArgumentException;

/**
 * Description of TypeStore
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class TypeStore {

    private $types;

    public function __construct() {
        $this->types = new ArrayObject();
    }

    /**
     * Make the container aware of given type, to use if needed.
     *
     * @param string $type
     */
    public function addType(string $type): void {
        if (!$this->typeExists($type)) {
            throw new InvalidArgumentException("Type '$type' class does not exist");
        }
        $this->types[] = $type;
    }

    public function getSuitableType(string $class): ?string {
        return $this->findSuitableType($this->types, $class);
    }

    private function findSuitableType(ArrayObject $types, string $class): ?string {
        foreach ($types as $type) {
            if (is_subclass_of($type, $class)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Check if the given type actually exists.
     *
     * @param string $type The class or interface name (inc. namespace)
     *
     * @return bool
     */
    private function typeExists(string $type): bool {
        return interface_exists($type) || class_exists($type);
    }

}
