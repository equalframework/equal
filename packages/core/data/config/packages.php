<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Provide the full list of packages present within the `packages` folder (initialized or not).',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context                  $context
 */
list($context) = [$providers['context']];

$packages = [];

if(!is_dir('packages') || !($list = scandir('packages'))) {
    throw new Exception('packages directory not found', QN_ERROR_INVALID_CONFIG);
}

foreach($list as $node) {
    if(is_dir('packages/'.$node) && !in_array($node, array('.', '..')) && $node[0] != '.') {
        $packages[] = $node;
    }
}

$context->httpResponse()
        ->body($packages)
        ->send();
