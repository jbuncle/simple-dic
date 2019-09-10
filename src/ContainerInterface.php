<?php

/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

/**
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
     * Add a factory method for the container to use when looking up a type.
     * 
     * The factory method's (callback's) parameters will be autowired.
     * 
     * If 'class' isn't defined, the return type will be looked up.
     * 
     * @param callable $method The callback.
     * @param string   $class  The return type (the type the callback provides).
     */
    public function addFactory(callable $method, string $class = '');

    /**
     * Tell the container to use the class defined in 'type', when the class 'for'
     * is defined.
     *
     * @param string $for     The class to map/alias
     * @param string $type    The type to use
     * @param bool $overwrite Whether to overwrite an existing mapping for 'for'
     *
     * @return void
     */
    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void;
}
