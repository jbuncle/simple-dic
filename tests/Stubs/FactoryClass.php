<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */
namespace JBuncle\SimpleDic\Stubs;

/**
 * FactoryClass
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class FactoryClass {

    public function __construct() {
    }

    public function getClass(): ClassWithProperties {
        return new ClassWithProperties('factory-val');
    }

    public static function getClassStatic(): ClassWithProperties {
        return new ClassWithProperties('factory-val');
    }

}
