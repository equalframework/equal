<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Request the final value of a specific configuration constant (as it is provided when a controller requests it). If the value is encrypted, the decrypted value is shown.',
    'params'        => [
        'constant' =>  [
            'type'          => 'string',
            'description'   => 'name of the constant for which the value is requested.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'    => 'private'
    ],
    'providers'     => [ 'context', 'report' ]
]);

/**
 * @var \equal\php\Context                $context
 * @var \equal\error\Reporter             $reporter
 */
list($context, $reporter) = [ $providers['context'], $providers['report'] ];

if(!\config\constant($params['constant']) && !defined($params['constant'])) {
    throw new Exception('unknown_property', QN_ERROR_INVALID_PARAM);
}

\config\export($params['constant']);

$context->httpResponse()
        ->status(200)
        ->body(['result' => constant($params['constant'])])
        ->send();
