<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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