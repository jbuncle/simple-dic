<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace JBuncle\SimpleDic\Stubs;

/**
 * ClassWithOptionalProperties
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ClassWithOptionalUntypedProperty {

    /**
     *
     * @var ?mixed
     */
    private $val;

    /**
     *
     * @param ?mixed $val
     */
    public function __construct($val = null) {
        $this->val = $val;
    }

    /**
     *
     * @return ?mixed
     */
    public function getVal() {
        return $this->val;
    }

}
