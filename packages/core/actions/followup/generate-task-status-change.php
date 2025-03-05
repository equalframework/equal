<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\followup\Task;
use core\followup\TaskModel;

[$params, $providers] = eQual::announce([
    'description'	=> "Generate task models' tasks when an entity status changes.",
    'params' 		=> [

        'entity' => [
            'type'              => 'string',
            'description'       => "Full name (including namespace) of the specific class to handle.",
            'usage'             => 'orm/entity',
            'required'          => true
        ],

        'entity_id' => [
            'type'              => 'integer',
            'description'       => "Id of the concerned object.",
            'required'          => true
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
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

if(!class_exists($params['entity'])) {
    throw new Exception("unknown_entity", EQ_ERROR_UNKNOWN_OBJECT);
}

$task_models = TaskModel::search(['entity', '=', $params['entity']])
    ->read([
        'name',
        'trigger_event_id' => [
            'event_type',
            'entity_status',
            'offset'
        ],
        'deadline_event_id' => [
            'event_type',
            'entity_date_field',
            'offset'
        ]
    ])
    ->get(true);

$task_models = array_filter(
    $task_models,
    function($task_model) {
        return $task_model['trigger_event_id']['event_type'] === 'status_change';
    }
);

if(!empty($task_models)) {
    $map_date_fields = [];
    foreach($task_models as $task_model) {
        if(isset($task_model['deadline_event_id']['entity_date_field'])) {
            $map_date_fields[$task_model['deadline_event_id']['entity_date_field']] = true;
        }
    }

    $date_fields = array_keys($map_date_fields);

    $obj = $params['entity']::id($params['entity_id'])
        ->read(array_merge($date_fields, ['status']))
        ->first();

    if(is_null($obj)) {
        throw new Exception("unknown_entity", EQ_ERROR_UNKNOWN_OBJECT);
    }

    foreach($task_models as $task_model) {
        if($task_model['trigger_event_id']['entity_status'] !== $obj['status']) {
            continue;
        }

        $visible_date = time() + (86400 * $task_model['trigger_event_id']['offset']);

        $deadline_date = null;
        if(isset($task_model['deadline_event_id'])) {
            if(isset($obj[$task_model['deadline_event_id']['entity_date_field']])) {
                $deadline_date = $obj[$task_model['deadline_event_id']['entity_date_field']] + (86400 * $task_model['deadline_event_id']['offset']);
            }
            else {
                // TODO: report problem date not set
            }
        }

        $task = Task::search([
            ['task_model_id', '=', $task_model['id']],
            ['entity', '=', $params['entity']],
            ['entity_id', '=', $params['entity_id']],
        ])
            ->read(['notes'])
            ->first();

        $notes = null;
        if(!is_null($task)) {
            // Keep notes of existing task, but remove it to be replaced
            $notes = $task['notes'];

            Task::id($task['id'])->delete();
        }

        Task::create([
            'name'          => $task_model['name'],
            'visible_date'  => $visible_date,
            'deadline_date' => $deadline_date,
            'task_model_id' => $task_model['id'],
            'entity'        => $params['entity'],
            'entity_id'     => $params['entity_id'],
            'notes'         => $notes
        ]);
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
