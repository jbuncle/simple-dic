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
class AutowireOptionalClass {

    /**
     *
     * @var ParentClass
     */
    private $parentClass;

    /**
     *
     * @var SubClass
     */
    private $subClass;

    public function __construct(ParentInterface $parentClass, SubClassInterface $subClass = null) {
        $this->parentClass = $parentClass;
        $this->subClass = $subClass;
    }

    public function getParentClass(): ParentClass {
        return $this->parentClass;
    }

    public function getSubClass(): ?SubClass {
        return $this->subClass;
    }

}
