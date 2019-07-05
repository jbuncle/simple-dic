<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
