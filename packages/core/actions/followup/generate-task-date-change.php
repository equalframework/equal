<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\followup\Task;
use core\followup\TaskModel;

[$params, $providers] = eQual::announce([
    'description'   => "Generate task models' tasks when the date changes.",
    'params'        => [

        'entity' => [
            'type'              => 'string',
            'description'       => "Full name (including namespace) of the specific class to export.",
            'help'              => "If left empty, all entities are exported.",
            'usage'             => 'orm/entity',
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

$tasks_models = TaskModel::search(['entity', '=', $params['entity']])
    ->read([
        'name',
        'trigger_event_id' => [
            'event_type',
            'entity_date_field',
            'offset'
        ],
        'deadline_event_id' => [
            'event_type',
            'entity_date_field',
            'offset'
        ]
    ])
    ->get(true);

$map_entities_tasks_models = [];

foreach($tasks_models as $tasks_model) {
    if($tasks_model['trigger_event_id']['event_type'] !== 'date_field') {
        continue;
    }

    if(!isset($map_entities_tasks_models[$tasks_model['trigger_event_id']['entity_date_field']])) {
        $map_entities_tasks_models[$tasks_model['trigger_event_id']['entity_date_field']] = [];
    }

    $map_entities_tasks_models[$tasks_model['trigger_event_id']['entity_date_field']][] = $tasks_model;
}

$today = date('Y-m-d', time());

foreach($map_entities_tasks_models as $entity => $tasks_models) {
    $date_fields = array_merge(
        array_map(
            function($task_model) { return $task_model['trigger_event_id']['entity_date_field']; },
            $tasks_models
        ),
        array_map(
            function($task_model) { return $task_model['deadline_event_id']['entity_date_field']; },
            $tasks_models
        )
    );

    $date_fields = array_filter(
        array_unique($date_fields),
        function($date_field) { return !is_null($date_field); }
    );

    $entities = $params['entity']::search()
        ->read($date_fields)
        ->first(true);

    if(empty($entities)) {
        continue;
    }

    foreach($tasks_models as $task_model) {
        foreach($entities as $entity) {
            $date = $entity[$task_model['trigger_event_id']['entity_date_field']] + (86400 * $task_model['trigger_event_id']['offset']);
            if(date('Y-m-d', $date) !== $today) {
                continue;
            }

            $visible_date = time();

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

            Task::create([
                'name'          => $task_model['name'],
                'visible_date'  => $visible_date,
                'deadline_date' => $deadline_date,
                'task_model_id' => $task_model['id'],
                'entity'        => $params['entity'],
                'entity_id'     => $entity['id']
            ]);
        }
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
