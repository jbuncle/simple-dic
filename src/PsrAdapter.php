<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * PsrAdapter
 *
 * @author jbuncle
 */
class PsrAdapter implements PsrContainerInterface, ContainerSetupInterface {

    /**
     *
     * @var Container
     */
    private $container;

    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id) {
        $this->container->getInstance($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool {
        $this->container->hasInstance($id);
    }

    public function addFactory(callable $method, string $class = ''): void {
        $this->container->addFactory($method, $class);
    }

    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        $this->container->addTypeMapping($for, $type, $overwrite);
    }

}
