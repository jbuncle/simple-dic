<?php

namespace SimpleDic\Stubs;

/**
 * Description of FactoryClass
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class FactoryClass {

    public function getClass(): ClassWithProperties {
        return new ClassWithProperties('factory-val');
    }
    public static function getClassStatic(): ClassWithProperties {
        return new ClassWithProperties('factory-val');
    }

}
