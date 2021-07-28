<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace JBuncle\SimpleDic;

use JBuncle\SimpleDic\Container;
use Psr\Container\ContainerInterface;

/**
 * PsrAdapter
 *
 * phpcs:disable SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
 *
 * @author jbuncle
 */
class PsrAdapter implements ContainerInterface, ContainerSetupInterface {

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
     * @return mixed Entry.
     */
    public function get(string $id) {
        return $this->container->getInstance($id);
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
        return $this->container->hasInstance($id);
    }

    public function addFactory(callable $method, string $class = ''): void {
        $this->container->addFactory($method, $class);
    }

    public function addTypeMapping(string $for, string $type, bool $overwrite = true): void {
        $this->container->addTypeMapping($for, $type, $overwrite);
    }

}
