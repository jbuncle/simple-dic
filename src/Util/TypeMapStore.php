<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
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

    /**
     *
     * @var \ArrayObject
     */
    private $subtypeCache;

    public function __construct() {
        $this->typeMappings = new ArrayObject();
        $this->keys = new \ArrayObject();
        $this->subtypeCache = new \ArrayObject();
    }

    public function getSuitableMapping(string $class): ?string {
        if ($this->hasMapping($class)) {
            return $this->getMapping($class);
        }

        // Search for a subtype
        return $this->lookupSubtype($class);
    }

    private function lookupSubtype(string $class): ?string {
        if ($this->subtypeCache->offsetExists($class)) {
            return $this->subtypeCache->offsetGet($class);
        }

        $subtype = TypeUtility::findSubType($this->keys, $class);
        // Store
        $this->subtypeCache->offsetSet($class, $subtype);
        // Return original
        return $subtype;
    }

    /**
     * Tell the container to use a specific type when another is requested.
     *
     * @param string $for The type we want to map
     * @param string $type The type to use
     */
    public function addTypeMapping(string $for, string $type, bool $overwrite = false): void {
        if (!TypeUtility::typeExists($for)) {
            throw new InvalidArgumentException("For '$for' class does not exist");
        }

        if (!TypeUtility::typeExists($type)) {
            throw new InvalidArgumentException("Type '$type' class does not exist");
        }

        if ($overwrite || !$this->typeMappings->offsetExists($for)) {
            $this->keys->append($for);
            $this->typeMappings->offsetSet($for, $type);
            // Clear subtype lookup cache
            $this->subtypeCache = new \ArrayObject();
        }
    }

    private function hasMapping(string $class): bool {
        return $this->typeMappings->offsetExists($class);
    }

    private function getMapping(string $class): ?string {
        return $this->typeMappings->offsetGet($class);
    }

}
