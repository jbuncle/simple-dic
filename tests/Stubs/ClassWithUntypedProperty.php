<?php

namespace SimpleDic\Stubs;

/**
 * ClassWithProperties
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ClassWithUntypedProperty {

    private $val;

    public function __construct($val) {
        $this->val = $val;
    }

    public function getVal() {
        return $this->val;
    }

}
