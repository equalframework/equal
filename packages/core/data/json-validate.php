<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;


[$params, $providers] = eQual::announce([
    'description'   => "Parse and validate a raw JSON structure against a given JSON-schema.",
    'params'        => [
        'json' =>  [
            'type'          => 'string',
            'usage'         => 'text/json.medium',
            'description'   => "Raw JSON to be validated against a given schema.",
            'required'      => true
        ],
        'schema_id' =>  [
            'type'          => 'string',
            'pattern'       => '/^urn:[a-z0-9_-]+:json-schema:[a-z0-9_-]+:[a-z0-9._-]+$/i',
            'description'   => 'Json-Schema $id following the syntax `urn:<namespace>:json-schema[:<package>]:<schema-name>`.',
            'help'          => "Target JSON schema is expected to be a https://json-schema.org/draft/2020-12/ descriptor for validation.",
            'required'      => true
        ],
        'package' => [
            'description'   => 'Package the schema relates to, if any.',
            'type'          => 'string',
            'usage'         => 'orm/package'
        ],
        'strict' =>  [
            'type'          => 'boolean',
            'description'   => 'Flag for enabling/disabling strict valiation (structure + types + mandatory).',
            'default'       => true
        ]
    ],
    'access' => [
        'visibility'        => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
$context = $providers['context'];

$schema = eQual::run('get', 'json-schema', ['id' => $params['schema_id']], false, true);

if (!$params['strict']) {

    $schema_data = json_decode($schema);

    // recursive function to remove "required" fields and make types nullable
    $normalize = function (&$node) use (&$normalize) {
        if(is_object($node)) {
            foreach($node as $key => &$value) {
                if($key === 'required') {
                    $value = [];
                }
                elseif($key === 'type') {
                    if(is_string($value)) {
                        $value = [$value, 'null'];
                    }
                    elseif (is_array($value) && !in_array('null', $value, true)) {
                        $value[] = 'null';
                    }
                }
                // recurse on sub-elements
                elseif(is_object($value) || is_array($value)) {
                    $normalize($value);
                }
            }
        }
        elseif(is_array($node)) {
            foreach($node as &$value) {
                if(is_object($value) || is_array($value)) {
                    $normalize($value);
                }
            }
        }
    };

    $normalize($schema_data);

    $schema = json_encode($schema_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}


$validator = new Validator();
$validator->setMaxErrors(10);

/** @var ValidationResult $result */
$result = $validator->validate(json_decode($params['json']), $schema);

$valid = $result->isValid();
$errors = [];

if(!$valid) {
    $errors = (new ErrorFormatter())->format($result->error());
}

$context->httpResponse()
        ->body([
            'result' => (bool) $valid,
            'errors' => $errors
        ])
        ->send();
