<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\alert\Message;

list($params, $providers) = eQual::announce([
    'description'   => "Remove the alert without retrying to perform related verification (issue might still be present).",
    'params'        => [
        'id' =>  [
            'description'   => 'Identifier of the alert to dismiss.',
            'type'          => 'integer',
            'required'      => true
        ]
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'auth', 'dispatch']
]);

/**
 * @var \equal\php\Context $context
 * @var \equal\auth\AuthenticationManager $auth
 * @var \equal\dispatch\Dispatcher $dispatch
 */
list($context, $auth, $dispatch) = [ $providers['context'], $providers['auth'], $providers['dispatch']];

$user_id = $auth->userId();

// If the alert is not found, the call is ignored. If a controller is mentioned in the alert it is called.
Message::id($params['id'])->delete(true);

$context->httpResponse()
        ->status(205)
        ->send();
