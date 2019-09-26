<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

/**
 * ContainerInterface
 *
 * @author jbuncle
 */
interface ContainerInterface {

    /**
     * Get an instance of the given class name.
     *
     * @param string $class The class name.
     * @return mixed
     * @throws InvalidArgumentException If the class name is for a non-existing type
     */
    public function getInstance(string $class);

    /**
     * Check if container has instance for given class.
     * @param string $class
     *
     * @return bool
     */
    public function hasInstance(string $class): bool;

}
