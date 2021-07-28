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
class ClassWithOptionalProperties {

    /**
     *
     * @var ?string
     */
    private $val;

    public function __construct(?string $val = null) {
        $this->val = $val;
    }

    public function getVal(): ?string {
        return $this->val;
    }

}
