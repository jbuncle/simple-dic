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
class AutowireOptionalClass {

    /**
     *
     * @var ParentClass
     */
    private $parentClass;

    /**
     *
     * @var SubClass
     */
    private $subClass;

    public function __construct(ParentInterface $parentClass, ?SubClassInterface $subClass = null) {
        $this->parentClass = $parentClass;
        $this->subClass = $subClass;
    }

    public function getParentClass(): ParentClass {
        return $this->parentClass;
    }

    public function getSubClass(): ?SubClass {
        return $this->subClass;
    }

}
