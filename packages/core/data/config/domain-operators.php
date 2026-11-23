<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\ObjectManager;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns schema of available operators and for each type',
    'params'        => [    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);


/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$operators = ObjectManager::$valid_operators;

$context
    ->httpResponse()
    ->body($operators)
    ->send();
