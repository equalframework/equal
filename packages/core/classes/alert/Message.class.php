<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\alert;

use equal\orm\Model;

class Message extends Model {

    public static function getColumns() {
        return [
            'object_class' => [
                'type'              => 'string',
                'description'       => "Class of entity targeted by the notification."
            ],

            'object_id' => [
                'type'              => 'integer',
                'description'       => "Identifier of the targeted object (of given class)."
            ],

            'message_model_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\alert\MessageModel',
                'description'       => "Identifier of the targeted model.",
                'required'          => true
            ],

            'severity' => [
                'type'              => 'string',
                'selection'         => [
                    'notice',           // weight = 1, might lead to a warning
                    'warning',          // weight = 2, might be important, might require an action
                    'important',        // weight = 3, requires an action
                    'urgent'            // weight = 4, requires immediate action
                ],
                'description'       => "Importance of the notification."
            ],

            'controller' => [
                'type'              => 'string',
                'description'       => "Full notation of the action controller to invoke (ex. core_example_action)."
            ],

            'params' => [
                'type'              => 'string',
                'description'       => "JSON object holding the parameters to relay to the controller.",
                'default'           => ''
            ],

            'links' => [
                'type'              => 'string',
                'description'       => "JSON list holding links (md format) to objects that relate to the message.",
                'default'           => ''
            ]

            // #memo
            // priority = age / 10 * weight
        ];
    }

    public function getUnique() {
        return [
            ['object_class', 'object_id', 'message_model_id']
        ];
    }    
}