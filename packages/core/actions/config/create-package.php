<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to create a new package using a given name.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package to be created (must be unique).',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
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

if(file_exists(EQ_BASEDIR.'/packages/'.$params['package'])) {
    throw new Exception('package_already_exists', EQ_ERROR_INVALID_PARAM);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package'], 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/actions", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/data", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/views", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/classes", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/init", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/init/routes", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/init/data", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/init/demo", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/i18n", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/tests", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
}

if(!mkdir(EQ_BASEDIR.'/packages/'.$params['package']."/uml", 0775)) {
    throw new Exception("directory_creation_failed", EQ_ERROR_UNKNOWN);
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

file_put_contents(EQ_BASEDIR.'/packages/'.$params['package'].'/manifest.json', $template);

$context
    ->httpResponse()
    ->status(204)
    ->send();
