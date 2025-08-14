<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\followup;

use core\User;
use equal\orm\Model;

class Task extends Model {

    public static function getDescription(): string {
        return "Task that has been or must be completed.";
    }

    public static function getColumns(): array {
        return [

            'name' => [
                'type'              => 'string',
                'description'       => "Name of the task.",
                'required'          => true
            ],

            'is_done' => [
                'type'              => 'boolean',
                'description'       => "Whether the task is done.",
                'default'           => false,
                'onupdate'          => 'onupdateIsDone'
            ],

            'done_by' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'The user who completed the task.',
                'visible'           => ['is_done', '=', true]
            ],

            'done_date' => [
                'type'              => 'date',
                'description'       => "Date on which the task was completed.",
                'visible'           => ['is_done', '=', true]
            ],

            'visible_date' => [
                'type'              => 'date',
                'description'       => "Date from which the task must be visible.",
                'default'           => function() { return strtotime('Today'); }
            ],

            'deadline_date' => [
                'type'              => 'date',
                'description'       => "Date on which the task as to be completed."
            ],

            'has_task_model' => [
                'type'              => 'boolean',
                'description'       => "Whether the task has a task model associated with it.",
                'default'           => false
            ],

            'trigger_event_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\followup\TaskEvent',
                'description'       => "The trigger event associated with the task.",
                'required'          => true
            ],

            'deadline_event_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\followup\TaskEvent',
                'description'       => "The deadline event associated with the task.",
                'domain'            => ['event_type', '=', 'date_field']
            ],

            'task_model_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\followup\TaskModel',
                'description'       => "The model used to create the task.",
                'help'              => "If null the Task was created manually.",
                'onupdate'          => 'onupdateTaskModel',
                'visible'           => ['has_task_model', '=', true]
            ],

            'notes' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => "Notes about the task."
            ],

            'object_class' => [
                'type'              => 'string',
                'description'       => "Namespace of the concerned entity.",
                'required'          => true
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => "Id of the associated entity.",
                'required'          => true
            ]

        ];
    }

    public static function onchange($event, $values): array {
        $result = [];
        if(isset($event['is_done'])) {
            if($event['is_done']) {
                $result['done_date'] = $values['done_date'] ?? strtotime('Today');

                /** @var \equal\auth\AuthenticationManager $auth */
                ['auth' => $auth] = \eQual::inject(['auth']);
                $user_id = $auth->userId();

                if($user_id) {
                    $user = User::id($user_id)
                        ->read(['id', 'name'])
                        ->first(true);

                    $result['done_by'] = $user ?? null;
                }
                else {
                    $result['done_date'] = null;
                    $result['done_by'] = null;
                }
            }
            else {
                $result['done_date'] = null;
                $result['done_by'] = null;
            }
        }

        return $result;
    }

    public static function onupdateIsDone($self) {
        /** @var \equal\auth\AuthenticationManager $auth */
        ['auth' => $auth] = \eQual::inject(['auth']);
        $user_id = $auth->userId();

        $self->read(['is_done', 'done_by', 'done_date']);
        foreach($self as $id => $task) {
            $done_data = [];
            if($task['is_done']) {
                if(is_null($task['done_by'])) {
                    $done_data['done_by'] = $user_id;
                }
                if(is_null($task['done_date'])) {
                    $done_data['done_date'] = strtotime('Today');
                }
            }
            else {
                if(!is_null($task['done_by'])) {
                    $done_data['done_by'] = null;
                }
                if(!is_null($task['done_date'])) {
                    $done_data['done_date'] = null;
                }
            }

            if(!empty($done_data)) {
                self::id($id)->update($done_data);
            }
        }
    }

    protected static function onupdateTaskModel($self) {
        $self->read(['task_model_id' => ['trigger_event_id', 'deadline_event_id']]);
        foreach($self as $id => $task) {
            $values = [];
            if(isset($task['task_model_id'])) {
                $values['has_task_model'] = true;
                if(isset($task['task_model_id']['trigger_event_id'])) {
                    $values['trigger_event_id'] = $task['task_model_id']['trigger_event_id'];
                }
                if(isset($task['task_model_id']['deadline_event_id'])) {
                    $values['deadline_event_id'] = $task['task_model_id']['deadline_event_id'];
                }
                self::id($id)->update($values);
            }
        }
    }
}
