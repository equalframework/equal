<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => "Tries to dismiss a message. Should be invoked as a user request for removing the message. If the situation is still occurring an identical alert will be re-created.",
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
['context' => $context, 'auth' => $auth, 'dispatch' => $dispatch] = $providers;

// #todo - restrict access based on link between MessageModel and user groups
$user_id = $auth->userId();

// If the alert is not found, the call is ignored. If a controller is mentioned in the alert it is called.
$dispatch->dismiss($params['id']);

$context->httpResponse()
        ->send();
