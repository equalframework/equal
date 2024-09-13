<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\import;

use equal\orm\Model;

class ColumnMapping extends Model {

    public static function getDescription(): string {
        return 'Mapping configuration that allows to import a specific column of an entity.';
    }

    public static function getColumns(): array {
        return [

            'entity_mapping_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\import\EntityMapping',
                'description'       => 'The parent EntityMapping this ColumnMapping is part of.',
                'required'          => true
            ],

            'origin_name' => [
                'type'              => 'string',
                'description'       => 'Name of the column where the data is to be found.'
            ],

            'origin_index' => [
                'type'              => 'integer',
                'usage'             => 'number/integer{0,255}',
                'description'       => 'Index of the column where the data is to be found.'
            ],

            'origin_cast_type' => [
                'type'              => 'string',
                'selection'         => [
                    'string'    => 'String',
                    'integer'   => 'Integer',
                    'float'     => 'Float',
                    'boolean'   => 'Boolean'
                ],
                'default'           => 'string'
            ],

            'target_name' => [
                'type'              => 'string',
                'description'       => 'Name of the target entity\'s column.',
                'required'          => true
            ],

            'data_transformers_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\import\DataTransformer',
                'foreign_field'     => 'column_mapping_id',
                'description'       => 'Mapping configurations defining how to import data to eQual entity.'
            ]

        ];
    }
}
