<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Task extends Model {

    /**
     * 	Tasks are executed by the CRON service if moment (timestamp) is lower or equal to the current time
     */
    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => 'Name of the task, as set at creation.',
                'default'           => ''
            ],
            'moment' => [
                'type'              => 'datetime',
                'required'          => true
            ],
            'controller' => [
                'type'              => 'string',
                'description'       => "Full notation of the action controller to invoke (ex. core_example_action)."
            ],
            'params' => [
                'type'              => 'string',
                'description'       => "un objet JSON object holding the parameters to relay to the controller."
            ]
        ];
    }
}