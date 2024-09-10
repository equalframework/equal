<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => "Anonymize an existing object with random data and given values.",
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package to anonymize.',
            'type'          => 'string',
            'usage'         => 'orm/package',
            'required'      => true
        ],
        'config_file' => [
            'description'   => 'Name of the configuration file to use to anonymize data.',
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
list('context' => $context) = $providers;

$data_folder = "packages/{$params['package']}/init/anonymize";

if(file_exists($data_folder) && is_dir($data_folder)) {
    // handle JSON files
    foreach(glob("$data_folder/*.json") as $json_file) {
        if(isset($params['config_file']) && $params['config_file'] !== $json_file) {
            continue;
        }

        $entities_config = file_get_contents($json_file);
        if(!empty($entities_config) && !isset($entities_config[0])) {
            $entities_config = [$entities_config];
        }

        foreach($entities_config as $entity_config) {
            eQual::run('do', 'core_model_anonymize', $entity_config);
        }
    }
}

$context->httpResponse()
        ->status(204)
        ->send();
