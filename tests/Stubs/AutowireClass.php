<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SimpleDic\Stubs;

/**
 * Description of AutowireClass
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class AutowireClass {

    /**
     *
     * @var ParentClass
     */
    private $parentClass;

    public function __construct(ParentClass $parentClass) {
        $this->parentClass = $parentClass;
    }

    public function getParentClass(): ParentClass {
        return $this->parentClass;
    }

}
