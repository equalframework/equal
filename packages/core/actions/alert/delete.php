<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\alert\Message;

[$params, $providers] = eQual::announce([
    'description'   => "Remove the alert without retrying to perform related verification (issue might still be present).",
    'params'        => [
        'id' => [
            'type'          => 'integer',
            'description'   => 'Identifier of the alert to dismiss.',
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
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$user_id = $auth->userId();

// If the alert is not found, the call is ignored. If a controller is mentioned in the alert it is called.
Message::id($params['id'])->delete(true);

$context
    ->httpResponse()
    ->status(205)
    ->send();
