<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */
namespace JBuncle\SimpleDic\Stubs;

/**
 * AutowireClass
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class AutowireClass {

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
