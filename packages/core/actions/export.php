<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use core\Translation;

[$params, $providers] = eQual::announce([
        'description'   => 'Export data from eQual database to initialization json files.',
        'help'          => '',
        'params'        => [
            'package' => [
                'description'   => 'Package that must be initialized (e.g. "core").',
                'help'          => 'All packages are exported if left empty.',
                'type'          => 'string',
                'usage'         => 'orm/package'
            ],
            'entity' => [
                'description'   => 'Full name (including namespace) of the class to import (e.g. "core\\User").',
                'help'          => 'All entities are exported if left empty.',
                'type'          => 'string',
                'usage'         => 'orm/entity'
            ],
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
        'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

/**
 * Methods
 */

/**
 * Returns all language codes except the default one
 *
 * @param string $default_lang_code
 * @return string[]
 */
$getTranslationLanguageCodes = function(string $default_lang_code): array {
    $languages = core\Lang::search(['code', '<>', $default_lang_code])
        ->read(['code'])
        ->get(true);

    return array_column($languages, 'code');
};

/**
 * Returns all entities of given package
 *
 * @param string $package
 * @return array
 */
$getPackageEntities = function(string $package): array {
    $directory = new RecursiveDirectoryIterator(EQ_BASEDIR."/packages/$package/classes");
    $iterator = new RecursiveIteratorIterator($directory);

    $regex = new RegexIterator($iterator, '/^.+\.class\.php$/i', RecursiveRegexIterator::GET_MATCH);

    $entities = [];
    foreach ($regex as $file) {
        $entities[] = $package . '\\' . str_replace('.class.php', '', substr($file[0], strlen(EQ_BASEDIR . "/packages/$package/classes/")));
    }

    return $entities;
};

/**
 * Returns entity fields except one2many because not needed for export
 *
 * @param array $model_schema
 * @return string[]
 */
$getEntityFieldsExceptOne2Many = function(array $model_schema): array {
    $fields = [];
    foreach($model_schema as $field => $field_descriptor) {
        if(!in_array('one2many', [$field_descriptor['type'], $field_descriptor['result_type'] ?? ''])) {
            $fields[] = $field;
        }
    }

    return $fields;
};

/**
 * Returns the translated data of the given entity for the given language
 *
 * @param class-string $entity
 * @param string $lang_code
 * @return array
 */
$getTranslationData = function(string $entity, string $lang_code): array {
    $translation_data = Translation::search(
        [
            ['object_class', '=', $entity],
            ['language', '=', $lang_code]
        ],
        ['sort' => ['object_id' => 'asc']]
    )
        ->read(['object_id', 'object_field', 'value'])
        ->get(true);

    $data = [];
    foreach($translation_data as $translation_item) {
        $item_index = null;
        foreach($data as $index => $item) {
            if($item['id'] === $translation_item['object_id']) {
                $item_index = $index;
            }
        }

        if(is_null($item_index)) {
            $data[] = ['id' => $translation_item['object_id']];
            $item_index = count($data) - 1;
        }

        $data[$item_index][$translation_item['object_field']] = $translation_item['value'];
    }

    return $data;
};

/**
 * Action
 */

$packages = eQual::run('get', 'core_config_live_packages');
if(empty($packages)) {
    throw new Exception('no_packages_initialized', EQ_ERROR_NOT_ALLOWED);
}

$export_folder_path = EQ_BASEDIR.'/exports/'.date('Y_m_d_His');
mkdir($export_folder_path, 0777, true);

$translation_language_codes = $getTranslationLanguageCodes(constant('DEFAULT_LANG'));

foreach($packages as $package) {
    if(isset($params['package']) && $params['package'] !== $package) {
        continue;
    }

    $entities = $getPackageEntities($package);
    foreach ($entities as $entity) {
        if(
            (isset($params['entity']) && $params['entity'] !== $entity)
            || $entity === 'core\Translation'
        ) {
            continue;
        }

        $model = $orm->getModel($entity);
        if(!$model) {
            trigger_error("ORM::$entity does not exist", EQ_REPORT_WARNING);
            continue;
        }

        $fields = $getEntityFieldsExceptOne2Many($model->getSchema());

        $data = $entity::search([])
            ->read($fields)
            ->get(true);

        if(empty($data)) {
            continue;
        }

        $init_data = [
            [
                'name'  => $entity,
                'lang'  => constant('DEFAULT_LANG'),
                'data'  => $data
            ]
        ];

        foreach($translation_language_codes as $lang_code) {
            $translation_data = $getTranslationData($entity, $lang_code);

            if(!empty($translation_data)) {
                $init_data[] = [
                    'name'  => $entity,
                    'lang'  => $lang_code,
                    'data'  => $translation_data
                ];
            }
        }

        $name = str_replace('\\', '_', $entity);
        file_put_contents(
            "$export_folder_path/$name.json",
            json_encode($init_data, JSON_PRETTY_PRINT)
        );
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
