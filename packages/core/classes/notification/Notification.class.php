<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\notification;

use equal\orm\Model;

class Notification extends Model {

    public static function getColumns(): array {
        return [

            'title' => [
                'type'              => 'string',
                'description'       => 'Title of the notification.'
            ],

            'message' => [
                'type'              => 'string',
                'usage'             => 'text/plain',
                'description'       => 'Message of the notification.'
            ],

            'user_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\User',
                'description'       => "User targeted by the Notification."
            ],

            'status' => [
                'type'              => 'string',
                'selection'         => [
                    'unread',
                    'read'
                ],
                'default'           => 'unread',
                'description'       => 'Current status of the Notification.'
            ],

            'is_persistent' => [
                'type'              => 'boolean',
                'description'       => 'Flag marking the notification as persistent (sticky).',
                'default'           => false
            ],

            'remind_frequency' => [
                'type'              => 'string',
                'description'       => 'Duration after dismiss before displaying a reminder to user.',
                'selection'         => [
                    'never',
                    'session',
                    'day'
                ],
                'visible'           => ['is_persistent', '=', true]
            ],

        ];
    }

    public static function getWorkflow(): array {
        return [

            'unread' => [
                'description' => 'The Notification is not considered read by the user.',
                'transitions' => [
                    'read' => [
                        'status'        => 'read',
                        'description'   => 'Mark the Notification as "read".'
                    ]
                ]
            ],

            'read' => [
                'description' => 'The Notification has been read or marked as read by the user.'
            ]

        ];
    }
}