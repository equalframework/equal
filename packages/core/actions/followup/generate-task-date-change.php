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
            'description'       => "Full name (including namespace) of the specific class to handle.",
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

$task_models = TaskModel::search(['entity', '=', $params['entity']])
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

$task_models = array_filter(
    $task_models,
    function($task_model) {
        return $task_model['trigger_event_id']['event_type'] === 'date_field';
    }
);

if(!empty($task_models)) {
    $map_date_fields = [];
    foreach($task_models as $task_model) {
        $map_date_fields[$task_model['trigger_event_id']['entity_date_field']] = true;
        if(isset($task_model['deadline_event_id']['entity_date_field'])) {
            $map_date_fields[$task_model['deadline_event_id']['entity_date_field']] = true;
        }
    }

    $date_fields = array_keys($map_date_fields);

    $today = date('Y-m-d', time());

    $objects = $params['entity']::search()
        ->read($date_fields)
        ->get();

    foreach($task_models as $task_model) {
        foreach($objects as $obj) {
            $date = $obj[$task_model['trigger_event_id']['entity_date_field']] + (86400 * $task_model['trigger_event_id']['offset']);
            if(date('Y-m-d', $date) !== $today) {
                continue;
            }

            $visible_date = time();

            $deadline_date = null;
            if(isset($task_model['deadline_event_id'])) {
                if(isset($obj[$task_model['deadline_event_id']['entity_date_field']])) {
                    $deadline_date = $obj[$task_model['deadline_event_id']['entity_date_field']] + (86400 * $task_model['deadline_event_id']['offset']);
                }
                else {
                    // TODO: report problem date not set
                }
            }

            Task::create([
                'name'          => $task_model['name'],
                'visible_date'  => $visible_date,
                'deadline_date' => $deadline_date,
                'task_model_id' => $task_model['id'],
                'entity'        => $params['entity'],
                'entity_id'     => $obj['id']
            ]);
        }
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
