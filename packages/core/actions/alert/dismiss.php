<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
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
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 * @var \equal\dispatch\Dispatcher          $dispatch
 */
list($context, $auth, $dispatch) = [ $providers['context'], $providers['auth'], $providers['dispatch']];

$user_id = $auth->userId();

// #todo - restrict access based on link between MessageModel and user groups

// If the alert is not found, the call is ignored. If a controller is mentioned in the alert it is called.
$dispatch->dismiss($params['id']);

$context->httpResponse()
        ->send();
