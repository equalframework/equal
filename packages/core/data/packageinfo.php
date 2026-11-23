<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Returns the descriptor of a specific package, based on its manifest file.',
    'params'        => [
        'package' => [
            'type'      => 'string',
            'required'  => true
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);
/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$package_path = EQ_BASEDIR.'/packages/'.$params['package'];
if(!file_exists($package_path)) {
    throw new Exception('unknown_package', EQ_ERROR_INVALID_PARAM);
}

$manifest_path = $package_path.'/manifest.json';
if(!file_exists($manifest_path)) {
    throw new Exception('missing_manifest', EQ_ERROR_INVALID_CONFIG);
}

$manifest = json_decode(file_get_contents($manifest_path), true, 512, JSON_BIGINT_AS_STRING);

if(!$manifest) {
    throw new Exception('invalid_manifest', EQ_ERROR_INVALID_CONFIG);
}

// send back basic info of the User object
$context
    ->httpResponse()
    ->body($manifest)
    ->send();
