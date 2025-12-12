<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\followup\Task;

[$params, $providers] = eQual::announce([
    'description'   => "Dismisses an alert related to given task if it is done.",
    'params'        => [

        'id' => [
            'type'              => 'integer',
            'description'       => "Identifier of the task that needs a check.",
            'required'          => true
        ],

        'message_model' => [
            'type'              => 'string',
            'description'       => "The name of the message model to use for the alert.",
            'default'           => 'core.followup.task.reminder'
        ],

        'severity' => [
            'type'              => 'string',
            'description'       => "Severity of the created alerts.",
            'selection'         => [
                'notice',
                'warning',
                'important',
                'urgent'
            ],
            'default'           => 'warning'
        ]

    ],
    'access'        => [
        'visibility'        => 'protected'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'dispatch']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\dispatch\Dispatcher  $dispatch
 */
['context' => $context, 'dispatch' => $dispatch] = $providers;

$task = Task::id($params['id'])
    ->read(['is_done', 'object_id', 'object_class'])
    ->first();

if(is_null($task)) {
    throw new Exception("unknown_task", EQ_ERROR_UNKNOWN_OBJECT);
}

if($task['is_done']) {
    eQual::run('do', 'core_alert_dismiss', ['id' => $task['id']]);
}
else {
    $dispatch->dispatch($params['message_model'], $task['object_class'], $task['object_id'], $params['severity'], 'core_followup_check-task-done', [
        'id'            => $task['id'],
        'message_model' => $params['message_model'],
        'severity'      => $params['severity']
    ]);
}

$context->httpResponse()
        ->status(200)
        ->send();
