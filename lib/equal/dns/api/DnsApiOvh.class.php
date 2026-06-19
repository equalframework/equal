<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\dns\api;

use equal\dns\DnsApi;
use equal\http\HttpRequest;

class DnsApiOvh extends DnsApi {

    /**
     * OVH API credentials
     *
     * This class requires an OVH API access token composed of:
     *
     * - Application Key
     * - Application Secret
     * - Consumer Key
     *
     * Create the token from the OVHcloud token creation page:
     *
     * https://auth.eu.ovhcloud.com/api/createToken
     *
     * Recommended access rights for managing DNS records on a specific zone:
     *
     * GET    /domain/zone/{zone}/record*
     * POST   /domain/zone/{zone}/record
     * PUT    /domain/zone/{zone}/record/*
     * POST   /domain/zone/{zone}/refresh
     * GET    /auth/time
     *
     * Example for zone "example.com":
     *
     * GET    /domain/zone/example.com/record*
     * POST   /domain/zone/example.com/record
     * PUT    /domain/zone/example.com/record/*
     * POST   /domain/zone/example.com/refresh
     * GET    /auth/time
     *
     * Do not store credentials directly in source code.
     * Provide them through environment variables, secured settings or another
     * non-versioned secret storage mechanism.
     */
    public function __construct(array $config = []) {
        $config = $this->mergeConfig(self::preset(), $config);
        parent::__construct($config);
    }

    public static function preset(): array {
        return [
            'base_url' => 'https://eu.api.ovh.com/1.0',

            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],

            'auth' => [
                'type' => 'ovh'
            ],

            'provider' => 'ovh',
            'allowed_zones' => [],
            'supported_types' => ['A'],
            'min_ttl' => 60,

            'auth_time_path' => '/auth/time',

            'endpoints' => [
                'list_records' => [
                    'method' => 'GET',
                    'path' => '/domain/zone/@zone/record',
                    'query' => [
                        'fieldType' => '@type',
                        'subDomain' => '@subdomain'
                    ],
                    'success' => [
                        'status_codes' => [200]
                    ]
                ],
                'read_record' => [
                    'method' => 'GET',
                    'path' => '/domain/zone/@zone/record/@id',
                    'success' => [
                        'status_codes' => [200]
                    ]
                ],
                'create_record' => [
                    'method' => 'POST',
                    'path' => '/domain/zone/@zone/record',
                    'body' => [
                        'fieldType' => '@type',
                        'subDomain' => '@subdomain',
                        'target' => '@target',
                        'ttl' => '@ttl'
                    ],
                    'success' => [
                        'status_codes' => [200, 201]
                    ]
                ],
                'update_record' => [
                    'method' => 'PUT',
                    'path' => '/domain/zone/@zone/record/@id',
                    'body' => [
                        'fieldType' => '@type',
                        'subDomain' => '@subdomain',
                        'target' => '@target',
                        'ttl' => '@ttl'
                    ],
                    'success' => [
                        'status_codes' => [200]
                    ]
                ],
                'delete_record' => [
                    'method' => 'DELETE',
                    'path' => '/domain/zone/@zone/record/@id',
                    'success' => [
                        'status_codes' => [200]
                    ]
                ],
                'refresh_zone' => [
                    'method' => 'POST',
                    'path' => '/domain/zone/@zone/refresh',
                    'success' => [
                        'status_codes' => [200]
                    ]
                ]
            ]
        ];
    }

    protected function applyAuthentication(HttpRequest $request, string $method, string $url, string $raw_body, array $options = []): void {
        foreach(['application_key', 'application_secret', 'consumer_key'] as $key) {
            if(empty($this->config[$key])) {
                throw new \Exception('missing_ovh_dns_config', EQ_ERROR_INVALID_CONFIG);
            }
        }

        $timestamp = $options['timestamp'] ?? $this->getOvhTimestamp();
        $signature = $this->signRequest($method, $url, $raw_body, $timestamp);

        $request
            ->header('X-Ovh-Application', $this->config['application_key'])
            ->header('X-Ovh-Consumer', $this->config['consumer_key'])
            ->header('X-Ovh-Timestamp', (string) $timestamp)
            ->header('X-Ovh-Signature', $signature);
    }

    protected function getOvhTimestamp(): int {
        $url = $this->buildUrl($this->config['auth_time_path'] ?? '/auth/time');
        $request = new HttpRequest("GET {$url}");
        $request->header('Accept', 'application/json');

        $response = $request->send();
        if($response->getStatusCode() !== 200) {
            throw new \Exception('failed_retrieving_ovh_time', EQ_ERROR_UNKNOWN);
        }

        $body = $response->body();
        if(is_array($body) && isset($body['value'])) {
            $body = $body['value'];
        }

        if(!is_numeric($body)) {
            throw new \Exception('invalid_ovh_time_response', EQ_ERROR_UNKNOWN);
        }

        return (int) $body;
    }

    protected function signRequest(string $method, string $url, string $raw_body, int $timestamp): string {
        $fingerprint = $this->config['application_secret']
            . '+'
            . $this->config['consumer_key']
            . '+'
            . strtoupper($method)
            . '+'
            . $url
            . '+'
            . $raw_body
            . '+'
            . $timestamp;

        return '$1$' . sha1($fingerprint);
    }
}
