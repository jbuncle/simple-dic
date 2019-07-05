<?php


/*
 * Copyright (C) 2019 James Buncle (https://jbuncle.co.uk) - All Rights Reserved
 */


namespace SimpleDic;

/**
 *
 * @author James Buncle <jbuncle@hotmail.com>
 */
interface ArgsInjector {

    public function getArgsForParams(array $params): array;
}
