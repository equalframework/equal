<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Ensure that a managed OVH DNS record exists for a client instance.',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'name' => [
            'description'   => 'Client instance subdomain name.',
            'type'          => 'string',
            'min'           => 3,
            'max'           => 17,
            'required'      => true
        ],
        'target' => [
            'description'   => 'DNS record target. Must be an IPv4 address.',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access' => [
        'visibility'        => 'private'
    ],
    'constants'     => ['DNS_API_APPLICATION_KEY', 'DNS_API_APPLICATION_SECRET', 'DNS_API_CONSUMER_KEY'],
    'providers'     => ['context', 'auth']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 */
['context' => $context, 'auth' => $auth] = $providers;

$dns_config = [
    'provider' => 'ovh',
    'zones'    => [
        'fmtsolutions.be' => [
            'provider'    => 'ovh',
            'credentials' => [
                'application_key'    => constant('DNS_API_APPLICATION_KEY'),
                'application_secret' => constant('DNS_API_APPLICATION_SECRET'),
                'consumer_key'       => constant('DNS_API_CONSUMER_KEY')
            ],
            'supported_types' => ['A'],
            'min_ttl'         => 60,
            'default_ttl'     => 3600
        ]
    ]
];

$protected_names = [
    '',
    '@',
    'www',
    'mail',
    'smtp',
    'imap',
    'pop',
    'ftp',
    'webmail',
    '_dmarc',
    '_acme-challenge',
    'platform'
];

$ttl = 3600;

$zone = 'fmtsolutions.be';
$name = \equal\dns\DnsRecordHelper::normalizeSubdomain($params['name'], $zone);

$type = 'A';
$value = trim($params['target']);

$logDnsOperation = function(string $operation, string $result, ?string $error = null, ?string $old_value = null) use ($zone, $name, $type, $value): void {
    $log = [
        'date'      => date('c'),
        'zone'      => $zone,
        'name'      => $name,
        'type'      => $type,
        'old_value' => $old_value,
        'new_value' => $value,
        'provider'  => 'ovh',
        'operation' => $operation,
        'result'    => $result,
        'error'     => $error
    ];

    trigger_error('APP::dns_operation ' . json_encode($log, JSON_UNESCAPED_SLASHES), $error ? EQ_REPORT_WARNING : EQ_REPORT_INFO);
};

try {
    if(!isset($dns_config['zones'][$zone])) {
        throw new \Exception('dns_zone_not_allowed', EQ_ERROR_NOT_ALLOWED);
    }

    if(in_array($name, $protected_names, true)) {
        throw new \Exception('protected_dns_record_name', EQ_ERROR_NOT_ALLOWED);
    }

    if(!preg_match('/^[a-z0-9][a-z0-9-]{2,16}$/', $name)) {
        throw new \Exception('invalid_instance_name', EQ_ERROR_INVALID_PARAM);
    }

    if($type === 'A' && filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
        throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
    }

    $dns = \equal\dns\Dns::create($dns_config);
    $existing_records = $dns->getRecords($zone, $name, $type);

    if(count($existing_records) > 1) {
        $logDnsOperation('ensure_record', 'conflict', 'multiple_matching_dns_records');

        $context->httpResponse()
            ->status(409)
            ->body([
                'status'  => 'conflict',
                'message' => 'Multiple matching DNS records found.'
            ])
            ->send();
        return;
    }

    $old_value = null;
    if(count($existing_records) === 1 && is_array($existing_records[0])) {
        $old_value = $existing_records[0]['value'] ?? $existing_records[0]['target'] ?? null;
    }

    $result = $dns->ensureRecord([
        'zone'  => $zone,
        'name'  => $name,
        'type'  => $type,
        'value' => $value,
        'ttl'   => $ttl
    ]);

    $logDnsOperation('ensure_record', $result['status'] ?? 'success', null, $old_value);

}
catch(\Exception $e) {
    $logDnsOperation('ensure_record', 'error', $e->getMessage());
    throw $e;
}


$context->httpResponse()
    ->status(204)
    ->send();
