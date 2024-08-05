<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\alert\Message;

list($params, $providers) = eQual::announce([
    'description'   => "Tries to dismiss a selection of alert message. Should be invoked as a user request for removing the message.",
    'params'        => [
        'ids' =>  [
            'description'       => 'Identifiers of the alerts to dismiss.',
            'type'              => 'one2many',
            'foreign_object'    => 'core\alert\Message',
            'required'          => true
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
    'providers'     => ['context', 'dispatch']
]);

/**
 * @var \equal\php\Context $context
 * @var \equal\dispatch\Dispatcher $dispatch
 */
list($context, $dispatch) = [ $providers['context'], $providers['dispatch']];

$messages = Message::ids($params['ids'])->read(['id']);

foreach($messages as $id => $message) {
    try {
        eQual::run('do', 'core_alert_dismiss', ['id' => $id]);
    }
    catch(Exception $e) {
        // something went wrong : ignore
    }
}

$context->httpResponse()
        ->send();
