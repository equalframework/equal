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
        return 'Transform step to adapt external data to eQual entity.';
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
                    'cast'                      => 'Cast',
                    'round'                     => 'Round',
                    'multiply'                  => 'Multiply',
                    'divide'                    => 'Divide',
                    'field-contains'            => 'Field contains',
                    'field-does-not-contain'    => 'Field does not contain',
                    'replace'                   => 'Replace',
                    'trim'                      => 'Trim',
                    'phone'                     => 'Phone'
                ],
                'description'       => 'The type of transform operation to be applied on a column data to import.',
                'default'           => 'value'
            ],

            'order' => [
                'type'              => 'integer',
                'description'       => 'For sorting the moments within a day.',
                'default'           => 1
            ],

            'value' => [
                'type'              => 'string',
                'description'       => 'A static value to set',
                'visible'           => ['transformer_type', '=', 'value']
            ],

            'cast_to' => [
                'type'              => 'string',
                'selection'         => [
                    'string'    => 'String',
                    'integer'   => 'Integer',
                    'float'     => 'Float',
                    'boolean'   => 'Boolean'
                ],
                'visible'           => ['transformer_type', '=', 'cast']
            ],

            'transform_by' => [
                'type'              => 'integer',
                'description'       => 'Value to multiply or divide by.',
                'visible'           => ['transformer_type', 'in', ['multiply', 'divide']]
            ],

            'field_contains_value' => [
                'type'              => 'string',
                'description'       => 'Value that must be found or not.',
                'visible'           => ['transformer_type', 'in', ['field-contains', 'field-does-not-contain']]
            ],

            'replace_search_value' => [
                'type'              => 'string',
                'description'       => 'Value that need to be replaced.',
                'visible'           => ['transformer_type', '=', 'replace']
            ],

            'replace_replace_value' => [
                'type'              => 'string',
                'description'       => 'Value that must replace the search value.',
                'visible'           => ['transformer_type', '=', 'replace']
            ],

            'phone_prefix' => [
                'type'              => 'string',
                'description'       => 'The prefix for phone number (e.g., 32).',
                'visible'           => ['transformer_type', '=', 'phone']
            ]

        ];
    }

    public static function transformValue(array $data_transformer, $value) {
        switch($data_transformer['transformer_type']) {
            case 'value':
                $value = $data_transformer['value'];
                break;
            case 'cast':
                switch($data_transformer['cast_to']) {
                    case 'string':
                        $value = (string) $value;
                        break;
                    case 'integer':
                        $value = (int) $value;
                        break;
                    case 'float':
                        $value = (float) $value;
                        break;
                    case 'boolean':
                        $value = (boolean) $value;
                        break;
                }
                break;
            case 'round':
                $value = round($value);
                break;
            case 'multiply':
                $value = $value * $data_transformer['transform_by'];
                break;
            case 'divide':
                $value = $value / $data_transformer['transform_by'];
                break;
            case 'field-contains':
                $value = strpos($value, $data_transformer['field_contains_value']) !== false;
                break;
            case 'field-does-not-contain':
                $value = strpos($value, $data_transformer['field_contains_value']) === false;
                break;
            case 'replace':
                $value = str_replace($data_transformer['replace_search_value'], $data_transformer['replace_replace_value'], $value);
                break;
            case 'trim':
                $value = trim($value);
                break;
            case 'phone':
                $value = str_replace(' ', '', $value);
                if(!empty($value)) {
                    $value = str_replace('.', '', $value);
                    if(strpos($value, '+') !== 0) {
                        if(strpos($value, '0') === 0) {
                            $value = substr($value, 1);
                        }

                        $value = '+'.$data_transformer['phone_prefix'].$value;
                    }
                }
                break;
        }

        return $value;
    }
}
