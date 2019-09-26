<?php declare(strict_types=1);
/**
 * Copyright (C) 2019 James Buncle (https://www.jbuncle.co.uk) - All Rights Reserved
 */

namespace SimpleDic\Stubs;

/**
 * ClassWithProperties
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
class ClassWithProperties {

    /**
     *
     * @var string
     */
    private $val;

    public function __construct(string $val) {
        $this->val = $val;
    }

    public function getVal(): string {
        return $this->val;
    }

}
