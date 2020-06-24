<?php
namespace equal\inventory\asset;


class Server extends \qinoa\orm\Model {
    public static function getColumns() {
        /**
        *
        */
        return [
            'hostname' => [
                'type'              => 'string',
                'description'       => "hostname of the server, if any"
            ],
            'IPv4' => [
                'type'              => 'string',
                'description'       => "Email address of the contact",
                'required'          => true
            ],
            'IPv6' => [
                'type'              => 'string',
                'description'       => "Phone number of the contact, if any",
                'required'          => true                
            ],
            'description' => [
                'type'              => 'string',
                'description'       => "memo to identify server (tech specs, hosting plan, ...)"
            ],
            'access' => [
                'type'              => 'one2many',            
                'foreign_object'    => 'equal\inventory\Access', 
                'foreign_field'     => 'server_id'
            ]
        ];
    }
}