<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\services\Container;

$container = Container::getInstance();
// retrieve required services
$context = $container->get(['context']);


$request = $context->getHttpRequest();

var_dump($request);
