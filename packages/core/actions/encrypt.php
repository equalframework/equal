<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

// announce script and fetch parameters values
list($params, $providers) = announce([
    'description'	=>	"Encrypts given value using private cipher key.",
    'params' 		=>	[
        'value' =>  [
            'description'   => 'Value to encrypt.',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);


// initalise local vars with inputs
list($context) = [ $providers['context'] ];

$result = config\encrypt($params['value']);

$context->httpResponse()
        ->body(['result' => $result])
        ->status(204)
        ->send();