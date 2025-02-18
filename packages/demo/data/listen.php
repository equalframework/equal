<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\services\Container;

$container = Container::getInstance();
// retrieve required services
$context = $container->get(['context']);


$request = $context->getHttpRequest();

var_dump($request);
