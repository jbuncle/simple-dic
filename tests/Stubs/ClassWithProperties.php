<?php

namespace SimpleDic\Stubs;

/**
 * ClassWithProperties
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ClassWithProperties {

    private $val;

    public function __construct(string $val) {
        $this->val = $val;
    }

    public function getVal() {
        return $this->val;
    }

}
