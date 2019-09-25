<?php

namespace SimpleDic\Stubs;

/**
 * lassTakingInterfaceImplementations
 *
 * @author jbuncle
 */
class ClassTakingInterfaceImplementations implements ParentInterface {

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
