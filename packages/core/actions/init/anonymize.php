<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, The eQual Framework, 2010-2024
    Author: The eQual Framework Contributors
    Original Author: Lucas Laurent
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Anonymize objects using json configuration files in "{package}/init/anonymize/".',
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package to anonymize.',
            'type'          => 'string',
            'usage'         => 'orm/package',
            'required'      => true
        ],
        'config_file' => [
            'description'   => 'Name of the configuration file to use to anonymize objects.',
            'help'          => 'Configuration file must match the format "{package}/init/anonymize/{config_file}.json".'
                                . ' If no config file specified, then all files of anonymize folder are used.',
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
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
['context' => $context] = $providers;

$data_folder = "packages/{$params['package']}/init/anonymize";

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
            if(!isset($class['name'])) {
                continue;
            }

            $anonymize_params = [
                'entity'    => $class['name'],
            ];
            foreach(['lang', 'fields', 'relations', 'domain'] as $param_key) {
                if(isset($class[$param_key])) {
                    $anonymize_params[$param_key] = $class[$param_key];
                }
            }

            eQual::run('do', 'core_model_anonymize', $anonymize_params);
        }
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
