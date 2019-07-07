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

    /**
     *
     * @var \ArrayObject
     */
    private $typeMappings;

    /**
     *
     * @var \ArrayObject
     */
    private $keys;

    public function __construct() {
        $this->typeMappings = new ArrayObject();
        $this->keys = new \ArrayObject();
    }

    public function getSuitableMapping(string $class): ?string {
        if ($this->hasMapping($class)) {
            return $this->getMapping($class);
        }
        return TypeUtility::findSubType($this->keys, $class);
    }

    /**
     * Tell the container to use a specific type when another is requested.
     *
     * @param string $for The type we want to map
     * @param string $type The type to use
     */
    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        if (!TypeUtility::typeExists($for)) {
            throw new InvalidArgumentException("For '$for' class does not exist");
        }
        if (!TypeUtility::typeExists($type)) {
            throw new InvalidArgumentException("Type '$type' class does not exist");
        }

        if ($overwrite || !array_key_exists($for, $this->typeMappings)) {
            $this->keys[] = $for;
            $this->typeMappings[$for] = $type;
        }
    }

    private function hasMapping(string $class): bool {
        return \array_key_exists($class, $this->typeMappings);
    }

    private function getMapping(string $class): string {
        return $this->typeMappings[$class];
    }

}
