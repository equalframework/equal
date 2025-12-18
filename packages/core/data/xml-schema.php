<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\php\Context;

[$params, $providers] = eQual::announce([
    'description'   => 'Return the XSD schema matching the provided schema id.',
    'params'        => [
        'id' => [
            'type'          => 'string',
            'description'   => 'XSD schema identifier (URN), e.g. `urn:iso:std:iso:20022:tech:xsd:pain.001.001.03`.',
            'help'          => 'Progressive resolution (most specific -> generic). Accepts "pain.001.001.03", ..., "urn:iso:std:iso:20022:tech:xsd:pain.001.001.03".',
            'required'      => true
        ],
        'package' => [
            'type'          => 'string',
            'usage'         => 'orm/package',
            'description'   => 'eQual package the schema belongs to.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/xml',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'    => 'protected'
    ],
    'providers'     => ['context']
]);

/**
 * @var Context $context
 */
$context = $providers['context'];

$schemaId = $params['id'];
$package  = $params['package'];

/**
 * Resolve XSD path from URN + package, using progressive override strategy.
 * Progressive resolution (most specific -> generic).
 *
 * Example:
 *   urn:iso:std:iso:20022:tech:xsd:pain.001.001.03
 *
 * Tries:
 *   iso-20022-tech-xsd-pain.001.001.03.xsd
 *   20022-tech-xsd-pain.001.001.03.xsd
 *   tech-xsd-pain.001.001.03.xsd
 *   xsd-pain.001.001.03.xsd
 *   pain.001.001.03.xsd
 */
$resolveXsd = function(string $schemaId, string $package): ?string {

    $urn = $schemaId;

    if(substr($schemaId, 0, 4) === 'urn:') {
        // Remove "urn:"
        $urn = substr($schemaId, 4);
    }

    // Split URN segments
    $parts = explode(':', $urn);

    if(count($parts) <= 0) {
        return null;
    }

    $base_dir = EQ_BASEDIR . '/packages/' . $package . '/schemas';
    if(!is_dir($base_dir)) {
        return null;
    }

    // Last segment = canonical schema name
    $schema_name = array_pop($parts);

    for($i = 0; $i <= count($parts); $i++) {
        $prefixParts = array_slice($parts, $i);
        $filename = '';

        if(!empty($prefixParts)) {
            $filename = implode('-', $prefixParts) . '-' . $schema_name . '.xsd';
        }
        else {
            $filename = $schema_name . '.xsd';
        }

        $path = $base_dir . '/' . $filename;

        if(is_file($path)) {
            return $path;
        }
    }

    return null;
};

// Resolve schema
$schema_path = $resolveXsd($schemaId, $package);

if(!$schema_path) {
    throw new Exception('schema_not_found', EQ_ERROR_INVALID_PARAM);
}

$result = file_get_contents($schema_path);

$context->httpResponse()
    ->body($result, true)
    ->send();
