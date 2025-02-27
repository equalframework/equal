<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
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