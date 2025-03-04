<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\followup\Task;
use core\followup\TaskModel;

[$params, $providers] = eQual::announce([
    'description'	=> "Generate task models' tasks when an entity change status.",
    'params' 		=> [

        'entity' => [
            'type'              => 'string',
            'description'       => "Full name (including namespace) of the specific class to export.",
            'help'              => "If left empty, all entities are exported.",
            'usage'             => 'orm/entity',
            'required'          => true
        ],

        'entity_id' => [
            'type'              => 'integer',
            'description'       => "Id of the concerned entity.",
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

$entity = $params['entity']::id($params['entity_id'])
    ->read(['status'])
    ->first();

if(is_null($entity)) {
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
            'entity_status',
            'entity_date_field',
            'offset'
        ]
    ])
    ->get();

foreach($task_models as $task_model) {
    if(
        $task_model['trigger_event_id']['event_type'] === 'status_change'
        && $task_model['trigger_event_id']['entity_status'] === $entity['status']
    ) {
        $visible_date = time() + (86400 * $task_model['trigger_event_id']['offset']);

        $deadline_date = null;
        if($task_model['deadline_event_id']['event_type'] === 'date_field') {
            $ent = $params['entity']::id($entity['id'])
                ->read([$task_model['deadline_event_id']['entity_date_field']])
                ->first(true);

            $deadline_date = $ent[$task_model['deadline_event_id']['entity_date_field']] + (86400 * $task_model['deadline_event_id']['offset']);
        }
        else {
            $deadline_date = time() + (86400 * $task_model['deadline_event_id']['offset']);
        }

        $task = Task::search([
            ['task_model_id', '=', $task_model['id']],
            ['entity', '=', $params['entity']],
            ['entity_id', '=', $entity['id']],
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
            'entity_id'     => $entity['id'],
            'notes'         => $notes
        ]);
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
