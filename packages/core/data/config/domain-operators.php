<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\ObjectManager;

list($params, $providers) = announce([
    'description'   => 'Returns schema of available operators and for each type',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'params'        => [
    ],
    'providers'     => ['context', 'orm']
]);


list($context, $orm) = [ $providers['context'], $providers['orm'] ];

$operators = ObjectManager::$valid_operators;

$context->httpResponse()
        ->body($operators)
        ->send();
