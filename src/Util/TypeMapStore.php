<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Util;

use ArrayObject;
use InvalidArgumentException;

/**
 * TypeMapStore
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class TypeMapStore {

    private $typeMap;

    public function __construct() {
        $this->typeMap = new ArrayObject();
    }

    public function hasMapping(string $class): bool {
        return \array_key_exists($class, $this->typeMap);
    }

    private function getMapping(string $class): string {
        return $this->typeMap[$class];
    }

    public function getSuitableMapping(string $class): ?string {
        if ($this->hasMapping($class)) {
            return $this->getMapping($class);
        }
        return $this->findSuitableType($this->getKeys($this->typeMap), $class);
    }

    /**
     * Tell the container to use a specific type when another is requested.
     *
     * @param string $for The type we want to map
     * @param string $type The type to use
     */
    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        if (!$this->typeExists($for)) {
            throw new InvalidArgumentException("For '$for' class does not exist");
        }
        if (!$this->typeExists($type)) {
            throw new InvalidArgumentException("Type '$type' class does not exist");
        }

        if ($overwrite || !array_key_exists($for, $this->typeMap)) {
            $this->typeMap[$for] = $type;
        }
    }

    /**
     * TODO: Move to util (and share)
     * Check if the given type actually exists.
     *
     * @param string $type The class or interface name (inc. namespace)
     *
     * @return bool
     */
    private function typeExists(string $type): bool {
        return interface_exists($type) || class_exists($type);
    }

    /**
     * TODO: Move to util and share
     * @param \SimpleDic\Util\ArrayObject $types
     * @param string $class
     * @return string|null
     */
    private function findSuitableType(ArrayObject $types, string $class): ?string {
        foreach ($types as $type) {
            if (is_subclass_of($type, $class)) {
                return $type;
            }
        }

        return null;
    }

    private function getKeys(\IteratorAggregate $objects): ArrayObject {
        $arr = new \ArrayObject();
        foreach ($objects as $key => $value) {
            $arr[] = $key;
        }
        return $arr;
    }

}
