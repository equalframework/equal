<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Returns the descriptor of a specific package, based on its manifest file.',
    'params'        => [
        'package' => [
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);
/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 * @var \equal\orm\ObjectManager            $om
 */
list($context, $om, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

$package_path = QN_BASEDIR.'/packages/'.$params['package'];
if(!file_exists($package_path)) {
    throw new Exception('unknown_package', QN_ERROR_INVALID_PARAM);
}

$manifest_path = $package_path.'/manifest.json';
if(!file_exists($manifest_path)) {
    throw new Exception('missing_manifest', QN_ERROR_INVALID_CONFIG);
}

$manifest = json_decode(file_get_contents($manifest_path), true, 512, JSON_BIGINT_AS_STRING);

if(!$manifest) {
    throw new Exception('invalid_manifest', QN_ERROR_INVALID_CONFIG);
}

// send back basic info of the User object
$context->httpResponse()
        ->body($manifest)
        ->send();
