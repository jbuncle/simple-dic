<?php

namespace SimpleDic\Stubs;

/**
 * ClassWithOptionalProperties
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ClassWithOptionalUntypedProperty {

    private $val;

    public function __construct($val = null) {
        $this->val = $val;
    }

    public function getVal() {
        return $this->val;
    }

}
