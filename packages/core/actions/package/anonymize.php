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
            'help'          => 'Configuration file must match the format "{package}/anonymize/{config_file}.json"',
            'type'          => 'string',
            'required'      => true
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

/**
 * Methods
 */

$getAnonymizeConfig = function($config_file_path): array {
    $config_file_content = file_get_contents($config_file_path);
    if(!$config_file_content) {
        throw new Exception('Missing anonymization config file ' . $config_file_path, EQ_ERROR_INVALID_CONFIG);
    }

    $import_config = json_decode($config_file_content, true);
    if(!is_array($import_config)) {
        throw new Exception('Invalid anonymization configuration file', EQ_ERROR_INVALID_CONFIG);
    }

    return $import_config;
};

/**
 * Action
 */

$entities_config = $getAnonymizeConfig(
    sprintf('%s/packages/%s/anonymize/%s.json', QN_BASEDIR, $params['package'], $params['config_file'])
);

if(!isset($entities_config[0])) {
    $entities_config = [$entities_config];
}

foreach($entities_config as $entity_config) {
    eQual::run('do', 'core_model_anonymize', $entity_config);
}

$context->httpResponse()
        ->status(204)
        ->send();
