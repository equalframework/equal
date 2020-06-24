<?php
namespace equal\inventory\asset;


class Software extends \qinoa\orm\Model {
    public static function getColumns() {
        /**
        *
        */
        return [
            'name' => [
                'type'              => 'string',
                'description'       => "Name of tje software",
                'required'          => true
            ],
            'edition' => [
                'type'              => 'string',
                'description'       => "Type of edition (CE/EE/Pro/...), if any"
            ],
            'version' => [
                'type'              => 'string',
                'description'       => "installed version of the software"
            ],
            'instance_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'equal\inventory\asset\Instance'
            ]            
        ];
    }
}