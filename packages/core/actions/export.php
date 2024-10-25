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
    'help'          => 'Result export files can be found in export folder at root of project.',
    'params'        => [
        'package' => [
            'description'   => 'Package that must be initialized (e.g. "core").',
            'help'          => 'If left empty, all packages are exported.',
            'type'          => 'string',
            'usage'         => 'orm/package'
        ],
        'entity' => [
            'description'   => 'Full name (including namespace) of the specific class to export (e.g. "core\\User").',
            'help'          => 'If left empty, all entities are exported.',
            'type'          => 'string',
            'usage'         => 'orm/entity'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context', 'orm', 'adapt']
]);

/**
 * @var \equal\php\Context                      $context
 * @var \equal\orm\ObjectManager                $orm
 * @var \equal\data\adapt\DataAdapterProvider   $dap
 */
['context' => $context, 'orm' => $orm, 'adapt' => $dap] = $providers;

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
 * Returns entity fields except one2many and not stored computed fields because not needed for export
 *
 * @param string $entity
 * @return string[]
 */
$getEntityFieldsToExport = function(string $entity) use($orm): array {
    $model = $orm->getModel($entity);
    $schema = $model->getSchema();
    $fields = [];
    foreach($schema as $field => $field_descriptor) {
        $isOne2ManyField = in_array('one2many', [$field_descriptor['type'], $field_descriptor['result_type'] ?? '']);
        $isNotStoredComputedField = $field_descriptor['type'] === 'computed' && $field_descriptor['store'] ?? false;

        if(!$isOne2ManyField && !$isNotStoredComputedField) {
            $fields[] = $field;
        }
    }

    return $fields;
};

/**
 * Returns the translated data of the given entity for the given language
 *
 * @param string $entity
 * @param string $lang
 * @return array
 */
$getTranslationData = function(string $entity, string $lang) use($orm, $dap): array {
    $adapter = $dap->get('json');
    $model = $orm->getModel($entity);

    $translation_data = Translation::search(
            [
                ['object_class', '=', $entity],
                ['language', '=', $lang]
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
        // convert to JSON according to field
        $f = $model->getField($translation_item['object_field']);
        $data[$item_index][$translation_item['object_field']] = $adapter->adaptOut($translation_item['value'], $f->getUsage(), $lang);
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

$timestamp = date('Ymd_His');

$export_folder_path = EQ_BASEDIR.'/export/'.$timestamp;
if(!mkdir($export_folder_path, 0754, true)) {
    throw new Exception(serialize(['folder_creation_error' => "unable to create output folder $path"]), EQ_ERROR_UNKNOWN);
}

$translation_language_codes = $getTranslationLanguageCodes(constant('DEFAULT_LANG'));

foreach($packages as $package) {
    if(isset($params['package']) && $params['package'] !== $package) {
        continue;
    }

    $entities = $getPackageEntities($package);
    foreach($entities as $entity) {
        if(
            (isset($params['entity']) && $params['entity'] !== $entity)
            || $entity === 'core\Translation' || $entity === 'core\Log'
        ) {
            continue;
        }

        try {
            $model = $orm->getModel($entity);
            if(!$model) {
                throw new Exception(serialize(['unknown_entity' => "$entity does not exist"]), EQ_REPORT_WARNING);
            }
            // #todo - load by batch of MAX objects and append to related JSON file
            $fields = $getEntityFieldsToExport($entity);
            $data = $entity::search([['state', 'in', ['instance', 'archive']], ['deleted', 'in', [0, 1]]])
                ->read($fields)
                ->adapt('json')
                ->get(true);
        }
        catch(Exception $e) {
            // unable to retrieve entity
            // SQL error / table does not exist
            continue;
        }

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

        foreach($translation_language_codes as $lang) {
            $translation_data = $getTranslationData($entity, $lang);

            if(!empty($translation_data)) {
                $init_data[] = [
                    'name'  => $entity,
                    'lang'  => $lang,
                    'data'  => $translation_data
                ];
            }
        }

        $name = str_replace('\\', '_', $entity);
        file_put_contents(
            "$export_folder_path/export_{$timestamp}_$name.json",
            json_encode($init_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
