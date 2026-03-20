<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\import\EntityMapping;

[$params, $providers] = eQual::announce([
    'description'   => 'Import eQual database data from a csv external source.',
    'params'        => [
        'entity_mapping_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'core\import\EntityMapping',
            'description'       => 'The entity mapping to use to adapt given data to eQual entities.',
            'required'          => true
        ],
        'csv_separator_character' => [
            'type'              => 'string',
            'default'           => ',',
            'min'               => 1,
            'max'               => 1
        ],
        'csv_enclosure_character' => [
            'type'              => 'string',
            'default'           => '"',
            'min'               => 1,
            'max'               => 1
        ],
        'csv_escape_character' => [
            'type'              => 'string',
            'default'           => '\\',
            'min'               => 1,
            'max'               => 1
        ],
        'data' => [
            'type'              => 'binary',
            'description'       => 'Payload of the file (raw file data).',
            'required'          => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['UPLOAD_MAX_FILE_SIZE'],
    'providers'     => ['context']
]);

['context' => $context] = $providers;

$entity_mapping = EntityMapping::id($params['entity_mapping_id'])
    ->read(['id', 'mapping_type'])
    ->first();

if(is_null($entity_mapping)) {
    throw new Exception('unknown_entity_mapping', EQ_ERROR_UNKNOWN_OBJECT);
}

if(strlen($params['data']) > constant('UPLOAD_MAX_FILE_SIZE')) {
    throw new Exception('maximum_size_exceeded', EQ_ERROR_INVALID_PARAM);
}

$data = [];

$lines = explode(PHP_EOL, $params['data']);

if($entity_mapping['mapping_type'] === 'index') {
    foreach($lines as $line) {
        if(!empty($line)) {
            $data[] = str_getcsv($line, $params['csv_separator_character'], $params['csv_enclosure_character'], $params['csv_escape_character']);
        }
    }
}
else {
    $header = array_shift($lines);
    $header_columns = str_getcsv($header, $params['csv_separator_character'], $params['csv_enclosure_character'], $params['csv_escape_character']);

    foreach($lines as $line) {
        if(empty($line)) {
            continue;
        }

        $row = str_getcsv($line, $params['csv_separator_character'], $params['csv_enclosure_character'], $params['csv_escape_character']);
        if(count($row) === count($header_columns)) {
            $data[] = array_combine($header_columns, $row);
        }
    }
}

eQual::run('do', 'core_model_import-mapped', [
    'entity_mapping_id' => $entity_mapping['id'],
    'data'              => $data
]);

$context->httpResponse()
        ->status(204)
        ->send();
