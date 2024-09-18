<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\import\EntityMapping;

[$params, $providers] = eQual::announce([
    'description'   => 'Import eQual database data from a json external source.',
    'params'        => [
        'entity_mapping_id' => [
            'type'              => 'many2one',
            'foreign_object'    => 'core\import\EntityMapping',
            'description'       => 'The entity mapping to use to adapt given data to eQual entities.',
            'required'          => true,
            'domain'            => ['mapping_type', '=', 'name']
        ],
        'data' => [
            'type'              => 'string',
            'description'       => 'Text JSON payload.',
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
    ->read(['id'])
    ->first();

if(is_null($entity_mapping)) {
    throw new Exception('unknown_entity_mapping', EQ_ERROR_UNKNOWN_OBJECT);
}

if(empty($params['data'])) {
    throw new Exception('empty_data', EQ_ERROR_INVALID_PARAM);
}

eQual::run('do', 'core_model_import-mapped', [
    'entity_mapping_id' => $entity_mapping['id'],
    'data'              => json_decode($params['data'], true)
]);

$context->httpResponse()
        ->status(204)
        ->send();
