<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\import;

use equal\orm\Model;

class DataTransformer extends Model {

    public static function getDescription(): string {
        return '.';
    }

    public static function getColumns(): array {
        return [

            'column_mapping_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\import\ColumnMapping',
                'description'       => 'The parent ColumnMapping this DataTransformer is part of.',
                'required'          => true
            ],

            'transformer_type' => [
                'type'              => 'string',
                'selection'         => [
                    'value'                     => 'Value',
                    'computed'                  => 'Computed',
                    'cast'                      => 'Cast',
                    'round'                     => 'Round',
                    'multiply'                  => 'Multiply',
                    'divide'                    => 'Divide',
                    'field-contains'            => 'Field contains',
                    'field-does-not-contain'    => 'Field contains'
                ],
                'description'       => 'The type of transform operation to be applied on a column data to import.',
                'default'           => 'value'
            ],

            'transformer_subtype' => [
                'type'              => 'string',
                'selection'         => [
                    'integer'   => 'Integer',
                    'boolean'   => 'Boolean',
                    'string'    => 'String'
                ]
            ],

            'order' => [
                'type'              => 'integer',
                'description'       => 'For sorting the moments within a day.',
                'default'           => 1
            ]

        ];
    }
}
