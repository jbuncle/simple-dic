<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace JBuncle\SimpleDic;

/**
 * ContainerFactory
 *
 * @author jbuncle
 */
class ContainerFactory {

    public function __construct() {
    }

    public function create(): Container {
        $container = new Container();
        // Add the container itself.
        $container->addInstance($container);
        return $container;
    }

}
