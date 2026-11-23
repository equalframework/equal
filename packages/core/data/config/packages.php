<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Provide the full list of packages present within the `packages` folder (initialized or not).',
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

$packages = [];

if(!is_dir('packages') || !($list = scandir('packages'))) {
    throw new Exception('missing_packages_directory', EQ_ERROR_INVALID_CONFIG);
}

foreach($list as $node) {
    if(is_dir('packages/'.$node) && !in_array($node, array('.', '..')) && $node[0] != '.') {
        $packages[] = $node;
    }
}

$context
    ->httpResponse()
    ->body($packages)
    ->send();
