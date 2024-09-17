<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\import;

use equal\orm\Model;

class EntityMapping extends Model {

    public static function getDescription(): string {
        return 'Mapping configuration that allows to import a specific entity.';
    }

    public static function getColumns(): array {
        return [

            'name' => [
                'type'              => 'string',
                'description'       => 'Import configuration mapping\'s name.',
                'required'          => true
            ],

            'entity' => [
                'type'              => 'string',
                'description'       => 'Namespace of the concerned entity.',
                'required'          => true
            ],

            'is_index_mapping' => [
                'type'              => 'boolean',
                'description'       => 'Is the data mapped by index or by name.',
                'default'           => true,
                'dependents'        => ['column_mappings_ids' => ['is_index_mapping']]
            ],

            'column_mappings_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'core\import\ColumnMapping',
                'foreign_field'     => 'entity_mapping_id',
                'description'       => 'Mapping configurations defining how to import data to eQual entity.'
            ]

        ];
    }
}
