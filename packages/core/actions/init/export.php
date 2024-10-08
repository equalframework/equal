<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Lucas LAURENT
    License: GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

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

$getLanguageCodesWithDefaultFirst = function($default_code) {
    $languages = core\Lang::search(['code', '<>', $default_code])
        ->read(['code'])
        ->get(true);

    return array_merge(
        [$default_code],
        array_column($languages, 'code')
    );
};

$packages = [];

$packages_file_path = 'log/packages.json';
if(file_exists($packages_file_path)) {
    $packages_json = file_get_contents($packages_file_path);
    $packages = array_keys(json_decode($packages_json, true));
}

$export_folder_path = EQ_BASEDIR.'/exports/'.date('Y_m_d_His');
mkdir($export_folder_path, 0777, true);

$language_codes = $getLanguageCodesWithDefaultFirst(constant('DEFAULT_LANG'));

foreach($packages as $package) {
    if(isset($params['package']) && $params['package'] !== $package) {
        continue;
    }

    $directory = new RecursiveDirectoryIterator(EQ_BASEDIR."/packages/$package/classes");
    $iterator = new RecursiveIteratorIterator($directory);

    $regex = new RegexIterator($iterator, '/^.+\.class\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach ($regex as $file) {
        $class = $package . '\\' . str_replace('.class.php', '', substr($file[0], strlen(EQ_BASEDIR . "/packages/$package/classes/")));

        if(isset($params['entity']) && $params['entity'] !== $class) {
            continue;
        }

        $model = $orm->getModel($class);
        if(!$model) {
            trigger_error("ORM::$class does not exist", EQ_REPORT_WARNING);
            continue;
        }

        $fields = [];
        $multilang_fields = [];
        foreach($model->getSchema() as $field => $field_descriptor) {
            if(!in_array('one2many', [$field_descriptor['type'], $field_descriptor['result_type'] ?? ''])) {
                $fields[] = $field;

                if($field_descriptor['multilang'] ?? false) {
                    $multilang_fields[] = $field;
                }
            }
        }

        $init_data = [];
        foreach($language_codes as $lang_code) {
            $data = $class::search([], [], $lang_code)
                ->read(constant('DEFAULT_LANG') === $lang_code ? $fields : $multilang_fields)
                ->get(true);

            if(empty($data)) {
                continue;
            }

            $fields_to_remove = [];
            if(constant('DEFAULT_LANG') !== $lang_code) {
                $fields_to_remove = array_diff(array_keys($data[0]), array_merge(['id'], $multilang_fields));
            }

            foreach($data as &$item) {
                foreach($fields_to_remove as $field) {
                    unset($item[$field]);
                }

                if(constant('DEFAULT_LANG') !== $lang_code) {
                    // Put id as first field
                    $item = array_merge(
                        ['id' => $item['id']],
                        $item
                    );
                }

                // TODO: get only translation
            }

            $init_data[] = ['name' => $class, 'lang' => $lang_code, 'data' => $data];
        }

        $name = str_replace('\\', '_', $class);
        file_put_contents(
            "$export_folder_path/$name.json",
            json_encode($init_data, JSON_PRETTY_PRINT)
        );
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
