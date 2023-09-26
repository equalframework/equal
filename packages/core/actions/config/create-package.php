<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Attempts to create a new package using a given name.",
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package to be created (must be unique).',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'providers'     => ['context', 'orm', 'access']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\orm\ObjectManager        $orm
 * @var \equal\access\AccessController  $ac
 */
list($context, $orm, $ac) = [$providers['context'], $providers['orm'], $providers['access']];

if(file_exists(QN_BASEDIR.'/packages/'.$params['package'])) {
    throw new Exception('package_already_exists', QN_ERROR_INVALID_PARAM);
}

if(!mkdir(QN_BASEDIR.'/packages/'.$params['package'], 0775)) {
    throw new Exception("directory_creation_failed", QN_ERROR_UNKNOWN);
}

// create empty manifest (from template)
$template = <<<EOT
{
    "name": "{$params['package']}",
    "description": "",
    "version": "1.0",
    "author": "",
    "license": "LGPL-3",
    "depends_on": [ "core" ],
    "apps": [ ],
    "tags": [ ]
}
EOT;

file_put_contents(QN_BASEDIR.'/packages/'.$params['package'].'/manifest.json', $template);

$context->httpResponse()
        ->status(204)
        ->send();
