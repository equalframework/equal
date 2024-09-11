<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Seed objects for package using json configuration files in "{package}/init/seed/".',
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package to seed.',
            'type'          => 'string',
            'usage'         => 'orm/package',
            'required'      => true
        ],
        'config_file' => [
            'description'   => 'Name of the configuration file to use to seed objects.',
            'help'          => 'Configuration file must match the format "{package}/init/seed/{config_file}.json".'
                                . ' If no config file specified, then all files of seed folder are used.',
            'type'          => 'string'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
list('context' => $context) = $providers;

$data_folder = "packages/{$params['package']}/init/seed";

if(file_exists($data_folder) && is_dir($data_folder)) {
    // handle JSON files
    foreach(glob("$data_folder/*.json") as $json_file) {
        if(isset($params['config_file']) && $params['config_file'] !== basename($json_file, '.json')) {
            continue;
        }

        $data = file_get_contents($json_file);
        $classes = json_decode($data, true);
        if(!$classes) {
            continue;
        }
        foreach($classes as $class) {
            if(!isset($class['name'], $class['qty'])) {
                continue;
            }

            $generate_params = [
                'entity'    => $class['name'],
            ];
            foreach(['lang', 'fields', 'relations', 'set_object_data'] as $param_key) {
                if(isset($class[$param_key])) {
                    $generate_params[$param_key] = $class[$param_key];
                }
            }

            $qty = is_array($class['qty']) ? mt_rand($class['qty'][0], $class['qty'][1]) : $class['qty'];
            for($i = 0; $i < $qty; $i++) {
                eQual::run('do', 'core_model_generate', $generate_params);
            }
        }
    }
}

$context->httpResponse()
        ->status(201)
        ->send();
