<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */
namespace JBuncle\SimpleDic\Stubs;

/**
 * ClassWithProperties
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ClassWithUntypedProperty {

    /**
     *
     * @var mixed
     */
    private $val;

    /**
     *
     * @param mixed $val
     */
    public function __construct($val) {
        $this->val = $val;
    }

    /**
     *
     * @return mixed
     */
    public function getVal() {
        return $this->val;
    }

}
