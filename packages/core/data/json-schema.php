<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Return the JSON schema matching the provided $id.',
    'params'        => [
        'id' => [
            'type'          => 'string',
            'description'   => 'Json-Schema $id following the syntax `urn:<namespace>:json-schema:<package>:<schema-name>`.',
            'pattern'       => '/^urn:[a-z0-9_-]+:json-schema:[a-z0-9_-]+:[a-z0-9._-]+$/i',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'access' => [
        'visibility'    => 'protected'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context               $context
 */
$context = $providers['context'];

$parseJsonSchemaId = function(string $id) {
    $pattern = '/^urn:[a-z0-9_-]+:json-schema:([a-z0-9_-]+):([a-z0-9._-]+)$/i';

    if(preg_match($pattern, $id, $matches)) {
        return [
            'package' => $matches[1],
            'schema'  => $matches[2]
        ];
    }
    // invalid format
    return null;
};

$info = $parseJsonSchemaId($params['id']);

if(!$info) {
    throw new Exception('invalid_id', EQ_ERROR_INVALID_PARAM);
}

$filename = EQ_BASEDIR . '/packages/' . $info['package'] . '/schemas/' . $info['schema'] . '.json';

if(!file_exists($filename)) {
    throw new Exception('no_matching_schema', EQ_ERROR_INVALID_PARAM);
}

$schema = file_get_contents($filename);

// send back basic info of the User object
$context->httpResponse()
        ->body($schema, true)
        ->send();
