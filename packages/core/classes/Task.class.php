<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Task extends Model {

    public static function getDescription() {
        return "Tasks can be scheduled through the scheduler service and are run when their 'moment' reaches current time.";
    }

    /**
     * 	Tasks are executed by the CRON service if moment (timestamp) is lower or equal to the current time
     */
    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => 'Name of the task, as set at creation.',
                'required'          => true
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'idle',
                    'running'
                ],
                'default'           => 'idle',
                'description'       => 'current status of the processing (to avoid concurrent executions).'
            ],

            'last_run' => [
                'type'              => 'datetime',
                'description'       => 'Moment at which the task was last executed.'
            ],

            'moment' => [
                'type'              => 'datetime',
                'description'       => 'Moment at which the task should be run for the next time.',
                'default'           => time()
            ],

            'is_active' => [
                'type'              => 'boolean',
                'description'       => 'Mark the task as (temporarily) active or inactive.',
                'default'           => true
            ],

            'is_recurring' => [
                'type'              => 'boolean',
                'description'       => 'Mark the task as recurring (to be run more than once).',
                'default'           => true
            ],

            'after_execution' => [
                'type'              => 'string',
                'selection'         => [
                    'delete',
                    'disable'
                ],
                'description'       => 'How to handle non-recurring task after execution.',
                'default'           => 'delete',
                'visible'           => ['is_recurring', '=', false]
            ],

            'repeat_axis' => [
                'type'              => 'string',
                'selection'         => [
                    'minute',
                    'hour',
                    'day',
                    'week',
                    'month',
                    'year'
                ],
                'default'           => 'day',
                'description'       => 'Basis for the repetition steps.',
                'visible'           => ['is_recurring', '=', true]
            ],

            'repeat_step' => [
                'type'              => 'integer',
                'default'           => 1,
                'description'       => 'Steps to wait before the tasks is run again.',
                'visible'           => ['is_recurring', '=', true]
            ],

            'controller' => [
                'type'              => 'string',
                'description'       => "Full notation of the action controller to invoke (ex. core_example_action)."
            ],

            'params' => [
                'type'              => 'string',
                'description'       => "JSON object holding the parameters to relay to the controller."
            ],

            'logs_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\TaskLog',
                'foreign_field'     => 'task_id'
            ]

        ];
    }

    public static function getConstraints() {
        return [
            'repeat_step' =>  [
                'invalid_step' => [
                    'message'       => 'Step must be positive and max value depends on axis (for years, max is 10).',
                    'function'      => function ($step, $values) {
                        // #todo - improve (we need to make sure repeat_axis is in $values)
                        return ($step >= 0 && $step < 60);
                    }
                ]
            ]
        ];
    }
}
