<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\alert\MessageModel;
use core\followup\Task;

[$params, $providers] = eQual::announce([
    'description'   => "Alerts followup tasks that should have be done by today.",
    'params'        => [

        'entity' => [
            'type'              => 'string',
            'description'       => "Full name (including namespace) of the specific class to handle.",
            'usage'             => 'orm/entity'
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

$domain = [
    ['is_done', '=', false],
    ['deadline_date', '=', time()]
];
if(!empty($params['entity'])) {
    $domain[] = ['object_class', '=', $params['entity']];
}

$tasks = Task::search($domain)
    ->read(['object_id', 'object_class'])
    ->get();

if(!empty($tasks)) {
    $message_model = MessageModel::search([
        ['name', '=', $params['message_model']]
    ])
        ->read(['name'])
        ->first();

    if(is_null($message_model)) {
        $message_model = MessageModel::create([
            'name'          => $params['message_model'],
            'label'         => "Task deadline has expired",
            'description'   => "A task was not handled within the required timeframe."
        ])
            ->read(['name'])
            ->first();
    }

    foreach($tasks as $id => $task) {
        $dispatch->dispatch($message_model['name'], $task['object_class'], $id, $params['severity'], 'core_followup_check-task-done', [
            'id'            => $id,
            'message_model' => $message_model['name'],
            'severity'      => $params['severity']
        ]);
    }
}

$context->httpResponse()
        ->status(200)
        ->send();
