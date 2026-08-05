<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Lang;
use core\Translation;
use equal\orm\ObjectManager;

[$params, $providers] = eQual::announce([
    'description'   => 'Returns stored translations for the multilang fields of a given object.',
    'params'        => [
        'entity' => [
            'description'   => 'Full name (including namespace) of the concerned entity.',
            'type'          => 'string',
            'usage'         => 'orm/entity',
            'required'      => true
        ],
        'id' => [
            'description'   => 'Unique identifier of the concerned object.',
            'type'          => 'integer',
            'required'      => true
        ],
        'field' => [
            'description'   => 'Optional multilang field to retrieve translations for.',
            'type'          => 'string'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'    => 'protected'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm', 'adapt']
]);

/**
 * @var \equal\php\Context                     $context
 * @var \equal\orm\ObjectManager               $orm
 * @var \equal\data\DataAdapterProvider        $dap
 */
['context' => $context, 'orm' => $orm, 'adapt' => $dap] = $providers;

if($params['id'] <= 0) {
    throw new Exception('object_invalid_id', EQ_ERROR_INVALID_PARAM);
}

$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
}

$schema = $model->getSchema();

$multilang_fields = [];
if(isset($params['field']) && $params['field'] !== '') {
    if(!isset($schema[$params['field']])) {
        throw new Exception('unknown_field', EQ_ERROR_INVALID_PARAM);
    }
    if(!($schema[$params['field']]['multilang'] ?? false)) {
        throw new Exception('non_multilang_field', EQ_ERROR_INVALID_PARAM);
    }
    $multilang_fields[] = $params['field'];
}
else {
    foreach($schema as $field => $descriptor) {
        if($descriptor['multilang'] ?? false) {
            $multilang_fields[] = $field;
        }
    }
}

$object = $params['entity']::id($params['id'])->read($multilang_fields)->first();

if(!$object) {
    throw new Exception('unknown_object', EQ_ERROR_UNKNOWN_OBJECT);
}

$result = [];

if(count($multilang_fields)) {
    /** @var \equal\data\adapt\DataAdapter */
    $adapter = $dap->get('json');

    $map_field_usages = [];
    foreach($multilang_fields as $field) {
        $map_field_usages[$field] = $model->getField($field)->getUsage();
    }

    $languages = Lang::search([], ['sort' => ['code' => 'asc']])->read(['code']);

    $default_lang = constant('DEFAULT_LANG');

    foreach($languages as $language) {
        $lang = $language['code'];

        $result[$lang] = [];

        foreach($multilang_fields as $field) {
            $result[$lang][$field] = null;
            if($lang === $default_lang) {
                $value = $object[$field] ?? null;
                $result[$lang][$field] = $adapter->adaptOut($value, $map_field_usages[$field], $lang);
            }
        }
    }

    $translations_ids = $orm->search(
        Translation::getType(),
        [
            ['object_class', '=', ObjectManager::getObjectRootClass($params['entity'])],
            ['object_id', '=', $params['id']],
            ['object_field', 'in', $multilang_fields]
        ],
        ['language' => 'asc', 'object_field' => 'asc']
    );

    $translations = $orm->read(Translation::getType(), $translations_ids, ['language', 'object_field', 'value']);

    foreach($translations as $translation) {
        $lang = $translation['language'];
        $field = $translation['object_field'];

        if(!isset($result[$lang])) {
            continue;
        }

        $result[$lang][$field] = $adapter->adaptOut(
            $translation['value'],
            $map_field_usages[$field],
            $lang
        );
    }
}

$context->httpResponse()
    ->body($result)
    ->send();
