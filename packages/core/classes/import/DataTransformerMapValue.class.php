<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
namespace core\import;

use equal\orm\Model;

class DataTransformerMapValue extends Model {

    public static function getDescription(): string {
        return 'Transform step to adapt external data to eQual entity.';
    }

    public static function getColumns(): array {
        return [

            'data_transformer_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'core\import\DataTransformer',
                'description'       => 'The data transformer this map value is part of.',
                'required'          => true
            ],

            'old_value' => [
                'type'              => 'string',
                'description'       => 'Old value that needs to be replaced.',
                'required'          => true
            ],

            'new_value' => [
                'type'              => 'string',
                'description'       => 'New value that needs to be replace the old value.',
                'required'          => true
            ]

        ];
    }
}
