<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
[$params, $providers] = eQual::announce([
    'type'          => 'do',
    'name'          => 'test_translation-consistency',
    'package_name'  => 'core',
    'description'   => 'Validate a model translation file against its JSON schema.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to validate (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'lang' =>  [
            'description'   => 'Language for which the translation file must be validated (iso639 code expected).',
            'type'          => 'string',
            'pattern'       => '/^[a-z]{2}(_[A-Z]{2})?$/',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
$context = $providers['context'];

$entity = str_replace('_', '\\', $params['entity']);
$parts = explode('\\', $entity);

if(count($parts) < 2) {
    throw new Exception('invalid_entity', EQ_ERROR_INVALID_PARAM);
}

$package = array_shift($parts);

if(!preg_match('/^[a-z0-9_-]+$/i', $package)) {
    throw new Exception('invalid_package', EQ_ERROR_INVALID_PARAM);
}

foreach($parts as $part) {
    if(!strlen($part) || in_array($part, ['.', '..'], true)) {
        throw new Exception('invalid_entity', EQ_ERROR_INVALID_PARAM);
    }
}

$translation_filename = EQ_BASEDIR . '/packages/' . $package . '/i18n/' . $params['lang'] . '/' . implode('/', $parts) . '.json';

if(!file_exists($translation_filename)) {
    throw new Exception('missing_translation', EQ_ERROR_INVALID_PARAM);
}

$json = file_get_contents($translation_filename);

if($json === false) {
    throw new Exception('translation_not_accessible', EQ_ERROR_INVALID_CONFIG);
}

$validation = eQual::run('get', 'json-validate', [
    'json'      => $json,
    'schema_id' => 'urn:equal:json-schema:core:model.translations'
]);

if(!($validation['result'] ?? false)) {
    throw new Exception('invalid_translation_json', EQ_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->status(204)
        ->send();
