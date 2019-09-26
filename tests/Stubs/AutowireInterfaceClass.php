<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Stubs;

/**
 * AutowireClass
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class AutowireInterfaceClass {

    /**
     *
     * @var ParentInterface
     */
    private $object;

    public function __construct(ParentInterface $parentClass) {
        $this->object = $parentClass;
    }

    public function getParentClass(): ParentInterface {
        return $this->object;
    }

}
