<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core;

use equal\orm\Model;

class Meta extends Model {

    public static function getDescription() {
        return "Meta values are used to store various additional information related to back-end or front-end components.";
    }

    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => 'Name of the meta (code+reference).',
                'function'          => 'calcName',
                'store'             => true
            ],

            'code' => [
                'type'              => 'string',
                'description'       => 'Identifier of the meta (arbitrary: \'workflow\', \'uml\', \'pipeline\', ...).',
                'dependencies'      => ['name']
            ],

            'reference' => [
                'type'              => 'string',
                'description'       => 'Custom reference (arbitrary: object_class, object_class.object_id, UML name, ...).',
                'dependencies'      => ['name']
            ],

            'value' => [
                'type'              => 'string',
                'usage'             => 'text/json',
                'description'       => 'Meta value (JSON formatted).'
            ]

        ];
    }

    public static function calcName($self) {
        $result = [];
        $self->read(['code', 'reference']);
        foreach($self as $id => $meta) {
            $result[$id] = $meta['code'].'.'.$meta['reference'];
        }
        return $result;
    }

}
