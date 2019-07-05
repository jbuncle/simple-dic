<?php

namespace SimpleDic\Stubs;

/**
 * ClassWithOptionalProperties
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ClassWithOptionalProperties {

    private $val;

    public function __construct(string $val = null) {
        $this->val = $val;
    }

    public function getVal() {
        return $this->val;
    }

}
