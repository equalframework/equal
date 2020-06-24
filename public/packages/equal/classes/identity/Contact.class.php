<?php
namespace equal\identity;


class Contact extends \qinoa\orm\Model {
    public static function getColumns() {
        /**
        *
        */

        return [
            'name' => [
                'type'              => 'string',
                'description'       => "Full name of the contact (must be a person, not a role)",
                'required'          => true                
            ],
            'email' => [
                'type'              => 'string',
                'description'       => "Email address of the contact",
                'required'          => true
            ],
            'phone' => [
                'type'              => 'string',
                'description'       => "Phone number of the contact, if any"
            ],            
            'function' => [
                'type'              => 'string',
                'description'       => "Role of the contact within the organisation (e.g. 'CEO', 'secreatary')"
            ],
            'organisation_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'equal\identity\Organisation',
                'description'       => "The organisation the contact belongs to",
                'required'          => true
            ]
        ];
    }
}