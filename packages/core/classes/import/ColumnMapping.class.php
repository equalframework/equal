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

            'mapping_type' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => 'Is the column data mapped by index or by name.',
                'store'             => true,
                'instant'           => true,
                'function'          => 'calcMappingType'
            ],

            'origin_name' => [
                'type'              => 'string',
                'description'       => 'Name of the column where the data is to be found.',
                'visible'           => ['mapping_type', '=', 'name']
            ],

            'origin_index' => [
                'type'              => 'integer',
                'usage'             => 'number/integer{0,255}',
                'description'       => 'Index of the column where the data is to be found.',
                'visible'           => ['mapping_type', '=', 'index']
            ],

            'origin' => [
                'type'              => 'computed',
                'result_type'       => 'string',
                'description'       => 'Index or name of the column where the data is to be found.',
                'store'             => false,
                'function'          => 'calcOrigin'
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

    public static function calcMappingType($self): array {
        $result = [];
        $self->read(['entity_mapping_id' => ['mapping_type']]);

        foreach($self as $id => $column_mapping) {
            $result[$id] = $column_mapping['entity_mapping_id']['mapping_type'];
        }

        return $result;
    }

    public static function calcOrigin($self): array {
        $result = [];
        $self->read(['mapping_type', 'origin_name', 'origin_index']);

        foreach($self as $id => $column_mapping) {
            $result[$id] = $column_mapping['mapping_type'] === 'index' ? $column_mapping['origin_index'] : $column_mapping['origin_name'];
        }

        return $result;
    }
}
