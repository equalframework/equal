<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Create a new empty view as a json file, for a given entity.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'package' => [
            'type'          => 'string',
            'usage'         => 'orm/package',
            'description'   => 'eQual package the update concerns.',
            'required'      => true
        ],
        'name' => [
            'description'   => 'Name of the update.',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context] = $providers;

// add timestamp to given file name
$file_name = date('YmdHis', time()) . '_' . str_replace(' ', '_', $params['name']);

// remove extension if given
$file_name = str_replace('.php', '', $file_name);

// create directory if missing
$directory = EQ_BASEDIR . "/packages/{$params['package']}/init/updates";
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

// create update file in given package
file_put_contents("$directory/$file_name.php", '');

$context
    ->httpResponse()
    ->status(201)
    ->send();
