<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\alert;

use equal\orm\Model;

class MessageModel extends Model {

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => 'Name of the model, for system identification.',
                'required'          => true
            ],

            'label' => [
                'type'              => 'string',
                'description'       => "Short name of the message model.",
                'multilang'         => true
            ],

            'description' => [
                'type'              => 'string',
                'description'       => "Description of the meaning of the message.",
                'multilang'         => true
            ],

            'type' => [
                'type'              => 'string',
                'description'       => "Short code/string for grouping the models."
            ],

            'messages_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\alert\Message',
                'foreign_field'     => 'message_model_id',
                'description'       => "List of messages of this model."
            ]

        ];
    }
}