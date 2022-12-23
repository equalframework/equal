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

            'object_link' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'usage'             => 'uri/url',
                'function'          => 'calcObjectLink',
                'description'       => "Link to the targeted entity.",
                'store'             => true
            ],

            'message_model_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\alert\MessageModel',
                'description'       => "Identifier of the targeted model.",
                'required'          => true
            ],

            'label' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Short name of the message model.",
                'function'          => 'calcLabel',
                'store'             => true,
                'multilang'         => true
            ],

            'description' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => "Description of the meaning of the message.",
                'function'          => 'calcDescription',
                'store'             => true,
                'multilang'         => true
            ],

            'severity' => [
                'type'              => 'string',
                'selection'         => [
                    'notice',           // weight = 1, might lead to a warning
                    'warning',          // weight = 2, might be important, might require an action
                    'important',        // weight = 3, requires an action
                    'error'             // weight = 4, requires immediate action
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
            ],

            // #memo
            // priority = age / 10 * weight

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => 'User recipient of the message, if any'
            ],

            'group_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\Group',
                'description'       => 'Group reciplient of the message, if any.'
            ]

        ];
    }

    public static function calcObjectLink($om, $oids, $lang) {
        $result = [];
        $messages = $om->read(__CLASS__, $oids, ['object_class', 'object_id'], $lang);

        foreach($messages as $mid => $message) {
            $model = $om->getModel($message['object_class']);
            $result[$mid] = str_replace('object.id', $message['object_id'], $model->getLink());
        }
        return $result;
    }

    public static function calcLabel($om, $oids, $lang) {
        $result = [];
        $messages = $om->read(__CLASS__, $oids, ['message_model_id.label'], $lang);

        foreach($messages as $mid => $message) {
            $result[$mid] = $message['message_model_id.label'];
        }
        return $result;
    }

    public static function calcDescription($om, $oids, $lang) {
        $result = [];
        $messages = $om->read(__CLASS__, $oids, ['message_model_id.description'], $lang);

        foreach($messages as $mid => $message) {
            $result[$mid] = $message['message_model_id.description'];
        }
        return $result;
    }

    public function getUnique() {
        return [
            ['object_class', 'object_id', 'message_model_id']
        ];
    }
}