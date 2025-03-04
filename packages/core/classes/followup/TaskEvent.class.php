<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\followup;

use equal\orm\Model;

class TaskEvent extends Model {

    public static function getDescription(): string {
        return "A task event associated with an entity status change or an entity date field value.";
    }

    public static function getColumns(): array {
        return [

            'name' => [
                'type'              => 'string',
                'description'       => "Name of the task event.",
                'required'          => true
            ],

            'entity' => [
                'type'              => 'string',
                'description'       => "Namespace of the concerned entity.",
                'required'          => true
            ],

            'event_type' => [
                'type'              => 'string',
                'description'       => "Event type.",
                'selection'         => ["status_change", "date_field"],
                'default'           => 'status_change'
            ],

            'entity_status' => [
                'type'              => 'string',
                'description'       => "Status of the entity the task event is associated with.",
                'visible'           => ['event_type', '=', 'status_change']
            ],

            'entity_date_field' => [
                'type'              => 'string',
                'description'       => "Date field of the entity the task event is associated with.",
                'visible'           => ['event_type', '=', 'date_field']
            ],

            'offset' => [
                'type'              => 'integer',
                'description'       => "The offset in days from the entity status change or field value.",
                'default'           => 0
            ],

            'trigger_event_task_models_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\followup\TaskModel',
                'foreign_field'     => 'trigger_event_id',
                'description'       => "List of task models that uses the event as a trigger."
            ],

            'deadline_event_task_models_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\followup\TaskModel',
                'foreign_field'     => 'deadline_event_id',
                'description'       => "List of task models that uses the event as a deadline."
            ]

        ];
    }
}
