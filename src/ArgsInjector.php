<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic;

/**
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
interface ArgsInjector {

    /**
     * Get instances for given array of ReflectionParameters.
     *
     * @param array<\ReflectionParameter> $params
     *
     * @return array<mixed>
     *
     * @throws ContainerException
     * @throws \SimpleDic\ContainerException
     */
    public function getArgsForParams(array $params): array;

}
