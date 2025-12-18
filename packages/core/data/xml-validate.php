<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\php\Context;

[$params, $providers] = eQual::announce([
    'description'   => 'Stand alone XML validator through XSD schema.',
    'params'        => [
        'xml' => [
            'type'          => 'string',
            'usage'         => 'text/xml.medium',
            'description'   => 'Raw XML content to validate.',
            'required'      => true
        ],
        'schema_id' => [
            'type'          => 'string',
            'description'   => 'XSD schema identifier (URN).',
            'required'      => true
        ],
        'package' => [
            'type'          => 'string',
            'usage'         => 'orm/package',
            'description'   => 'eQual package the schema belongs to.',
            'required'      => true
        ],
        'strict' => [
            'type'          => 'boolean',
            'description'   => 'Enable strict validation (fail on any error).',
            'default'       => true
        ]
    ],
    'access' => [
        'visibility' => 'protected'
    ],
    'response' => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers' => ['context']
]);

/**
 * @var Context $context
 */
$context = $providers['context'];

$xml      = $params['xml'];
$schemaId = $params['schema_id'];
$package  = $params['package'];
$strict   = $params['strict'];

// Retrieve XSD via xml-schema
$xsd = eQual::run('get', 'xml-schema', [
    'id'      => $schemaId,
    'package' => $package
]);

if(!$xsd || !is_string($xsd)) {
    throw new Exception('schema_not_found', EQ_ERROR_INVALID_PARAM);
}

// XML validation
libxml_use_internal_errors(true);

$dom = new DOMDocument('1.0', 'UTF-8');
$dom->preserveWhiteSpace = false;

$errors = [];
$valid  = false;

// Load XML
if(!$dom->loadXML($xml)) {
    foreach(libxml_get_errors() as $error) {
        $errors[] = [
            'code'    => 'xml_parse_error',
            'level'   => $error->level,
            'line'    => $error->line,
            'message' => trim($error->message)
        ];
    }
    libxml_clear_errors();
}
else {
    // Validate against XSD (string)
    $valid = $dom->schemaValidateSource($xsd);

    if (!$valid) {
        foreach (libxml_get_errors() as $error) {
            $errors[] = [
                'code'    => 'xsd_validation_error',
                'level'   => $error->level,
                'line'    => $error->line,
                'message' => trim($error->message)
            ];
        }
        libxml_clear_errors();
    }
}

// Strict mode handling
if($strict && !empty($errors)) {
    $valid = false;
}

$context->httpResponse()
    ->body([
        'result' => $valid,
        'errors' => $errors
    ])
    ->send();
