<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'description'	=>	"Decrypts given cipher using private cipher key.",
    'params' 		=>	[
        'value' =>  [
            'description'   => 'Cipher to decrypt.',
            'type'          => 'string',
            'required'      => true
        ],
        'legacy' => [
            'description'   => 'Use the legacy OpenSSL decryption scheme.',
            'type'          => 'boolean',
            'default'       => false
        ]
    ],
    'access'        => [
        'visibility'    => 'private'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);


// initalise local vars with inputs
['context' => $context] = $providers;

$result = config\decrypt($params['value'], $params['legacy']);

$context->httpResponse()
        ->body(['result' => $result])
        ->status(204)
        ->send();
