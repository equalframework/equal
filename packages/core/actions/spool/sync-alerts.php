<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\Mail;

[$params, $providers] = eQual::announce([
    'description' => "Monitor failed email deliveries and maintain related system alerts.",
    'help'        => "This controller scans emails marked as failing for more than one hour and ensures that a corresponding system alert exists for each affected object.\n
                    If a failing email already has an associated alert, it is preserved. If not, a new alert is created.\n
                    Alerts that no longer correspond to any failing email are automatically removed.\n
                    This controller is intended to be executed periodically (cron job) as part of the email delivery monitoring process.",
    'params'        => [],
    'access' => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'cron', 'dispatch']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\orm\ObjectManager        $orm
 * @var \equal\cron\Scheduler           $cron
 * @var \equal\dispatch\Dispatcher      $dispatch
 */
['context' => $context, 'orm' => $orm, 'cron' => $cron, 'dispatch' => $dispatch] = $providers;

$now = time();

$mails = Mail::search([
        ['status', '=', 'failing'],
        ['created', '<', $now - 3600]
    ])
    ->read(['status', 'object_class', 'object_id']);

// retrieve ID of specific message model for this controller
$message_models_ids = $orm->search('core\alert\MessageModel', ['name', '=', 'core.system.failed_email_sending']);

if(count($message_models_ids) <= 0) {
    throw new Exception('missing_mandatory_message_model', EQ_ERROR_INVALID_CONFIG);
}

$message_model_id = reset($message_models_ids);

$messages_ids = $orm->search('core\alert\Message', ['message_model_id', '=', $message_model_id]);
$messages = $orm->read('core\alert\Message', $messages_ids, ['object_class', 'object_id']);

$map_messages = [];
$map_messages_ids = [];
foreach($messages as $message_id => $message) {
    $map_messages_ids[$message_id] = true;
    $map_messages[$message['object_class']][$message['object_id']] = $message_id;
}

foreach($mails as $id => $mail) {
    if(isset($map_messages[$mail['object_class']][$mail['object_id']])) {
        $message_id = $map_messages[$mail['object_class']][$mail['object_id']];
        unset($map_messages_ids[$message_id]);
        continue;
    }
    $dispatch->dispatch('core.system.failed_email_sending', $mail['object_class'], $mail['object_id'], 'important', 'core_spool_check-emails');
}

// #memo - $map_messages_ids now points only to alerts that no longer relate to a failing email
if(!empty($map_messages_ids)) {
    $orm->delete('core\alert\Message', array_keys($map_messages_ids), true);
}

$context->httpResponse()
        ->status(204)
        ->send();
