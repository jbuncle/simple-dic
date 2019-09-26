<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

/**
 * ContainerSetupInterface
 *
 * @author jbuncle
 */
interface ContainerSetupInterface {

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
    public function addFactory(callable $method, string $class = ''): void;

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
