<?php
namespace equal\identity;


class Establishment extends \qinoa\orm\Model {
    public static function getColumns() {

        return [
            'name' => [
                'type'              => 'string',
                'description'       => "Full name of the contact (must be a person, not a role)",
                'required'          => true                
            ],
            'organisation_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'equal\identity\Organisation',
                'description'       => "The organisation the establishment belongs to",
                'required'          => true
            ]
        ];
    }
}