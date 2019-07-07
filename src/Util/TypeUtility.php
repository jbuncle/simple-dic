<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Util;

use ArrayObject;

/**
 * Description of TypeUtility
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class TypeUtility {

    /**
     * Check if the given type actually exists.
     *
     * @param string $type The class or interface name (inc. namespace)
     *
     * @return bool
     */
    public static function typeExists(string $type): bool {
        return interface_exists($type) || class_exists($type);
    }

    public static function isCallableAMethod(callable $val): bool {
        return is_array($val);
    }

    /**
     * @param \SimpleDic\Util\ArrayObject $types
     * @param string $class
     * @return string|null
     */
    public static function findSubType(ArrayObject $types, string $class): ?string {
        foreach ($types as $type) {
            if (\is_subclass_of($type, $class)) {
                return $type;
            }
        }

        return null;
    }

}
