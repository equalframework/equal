<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class TaskLog extends Model {

    public static function getDescription() {
        return "A TaskLog is an entry that relates to a task. One TaskLog is created after each execution of a task.";
    }

    /**
     * 	Tasks are executed by the CRON service if moment (timestamp) is lower or equal to the current time
     */
    public static function getColumns() {
        return [

            'task_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\Task',
                'description'       => 'The Task the log entry refers to.',
                'required'          => true
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'success',
                    'error'
                ],
                'description'       => 'Status depending on the Task execution outcome.'
            ],

            'log' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => "The value returned at the execution of the controller targeted by the Task."
            ]

        ];
    }
}
