<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\dns;

use equal\http\HttpRequest;

/**
 * Generic REST DNS provider.
 *
 * Endpoint mappings use `@field` placeholders from a normalized DNS record:
 * zone, subdomain, type, target, ttl, id.
 */
class DnsApi implements DnsProviderInterface {

    protected array $config = [];

    protected array $defaults = [
        'base_url' => null,

        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ],

        'auth' => [
            'type' => 'header',
            'name' => 'api-key'
        ],

        'success' => [
            'status_codes' => [200, 201, 202, 204]
        ],

        'allowed_zones' => [],
        'supported_types' => ['A'],
        'min_ttl' => 60,

        'endpoints' => []
    ];

    public function __construct(array $config = []) {
        $this->config = $this->mergeConfig($this->defaults, $config);
    }

    public function ensureRecord(array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);

        $records = $this->getRecords($record['zone'], $record['name'], $record['type'], $options);
        if(count($records) > 1) {
            throw new \Exception('dns_record_conflict', EQ_ERROR_CONFLICT_OBJECT);
        }

        if(count($records) === 1) {
            $id = $this->extractRecordId($records[0]);
            $existing = $this->readRecord($id, $record, $options);

            if($this->recordMatches($existing, $record)) {
                $result = $existing;
                $status = 'unchanged';
            }
            else {
                $result = $this->updateRecord($id, $record, $options);
                $status = 'updated';
            }
        }
        else {
            $result = $this->createRecord($record, $options);
            $id = $this->extractRecordId($result, false);
            $status = 'created';
        }

        if($status !== 'unchanged') {
            $this->refreshZone($record['zone'], $options);
        }

        return $this->formatRecordResult($status, $record, $id, $result);
    }

    public function getRecords(string $zone, string $name, string $type = 'A', array $options = []): array {
        $context = DnsRecordHelper::normalizeCriteria([
            'zone'      => $zone,
            'name'      => $name,
            'type'      => $type
        ], [
            'allowed_zones'   => $options['allowed_zones'] ?? $this->config['allowed_zones'] ?? [],
            'supported_types' => $options['supported_types'] ?? $this->config['supported_types'] ?? ['A']
        ]);

        return $this->callEndpoint('list_records', $context, $options);
    }

    public function createRecord(array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        return $this->callEndpoint('create_record', $record, $options);
    }

    public function readRecord($id, array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        $record['id'] = $id;

        return $this->callEndpoint('read_record', $record, $options, false);
    }

    public function updateRecord($id, array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        $record['id'] = $id;

        return $this->callEndpoint('update_record', $record, $options);
    }

    public function deleteRecord($id, array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        $record['id'] = $id;

        return $this->callEndpoint('delete_record', $record, $options);
    }

    public function refreshZone(string $zone, array $options = []): array {
        $zone = DnsRecordHelper::normalizeZone($zone);
        if($zone === '') {
            throw new \Exception('missing_dns_zone', EQ_ERROR_MISSING_PARAM);
        }

        $allowed_zones = DnsRecordHelper::normalizeStringList($options['allowed_zones'] ?? $this->config['allowed_zones'] ?? []);
        if(count($allowed_zones) && !in_array($zone, $allowed_zones, true)) {
            throw new \Exception('dns_zone_not_allowed', EQ_ERROR_NOT_ALLOWED);
        }

        return $this->callEndpoint('refresh_zone', ['zone' => $zone], $options, false);
    }

    public function request(string $method, string $path, $body = null, array $options = []): array {
        $method = strtoupper($method);
        $url = $this->buildUrl($path, $options['query'] ?? []);
        $raw_body = $this->encodeBody($body);

        $response = $this->sendRequest($method, $url, $raw_body, $options);
        $status = $response->getStatusCode();

        $success_codes = $options['success']['status_codes'] ?? $this->config['success']['status_codes'];
        if(!in_array($status, $success_codes, true)) {
            $this->logRequestFailure('request', $method, $url, $status, $response->body());
            throw new \Exception('dns_request_failed', EQ_ERROR_UNKNOWN);
        }

        return [
            'status' => $status,
            'body'   => $response->body()
        ];
    }

    public function supports(string $type): bool {
        $type = strtoupper(trim($type));
        $supported_types = DnsRecordHelper::normalizeStringList($this->config['supported_types'] ?? ['A'], true);

        return in_array($type, $supported_types, true);
    }

    protected function normalizeRecord(array $record, array $options = []): array {
        return DnsRecordHelper::normalizeRecord($record, [
            'allowed_zones'   => $options['allowed_zones'] ?? $this->config['allowed_zones'] ?? [],
            'supported_types' => $options['supported_types'] ?? $this->config['supported_types'] ?? ['A'],
            'min_ttl'         => $options['min_ttl'] ?? $this->config['min_ttl'] ?? 60
        ]);
    }

    protected function callEndpoint(string $name, array $context, array $options = [], bool $required = true): array {
        $endpoint = $this->config['endpoints'][$name] ?? null;
        if(!$endpoint) {
            if($required) {
                throw new \Exception('unsupported_dns_operation', EQ_ERROR_INVALID_CONFIG);
            }
            return [];
        }

        $method = strtoupper($endpoint['method'] ?? 'GET');
        $path = $this->resolveTemplate($endpoint['path'] ?? '', $context, true);
        $query = $this->resolveMap($endpoint['query'] ?? [], $context);
        $body = $this->resolveMap($endpoint['body'] ?? [], $context);

        $url = $this->buildUrl($path, $query);
        $raw_body = $this->encodeBody($method === 'GET' ? null : $body);
        $response = $this->sendRequest($method, $url, $raw_body, $options);

        $status = $response->getStatusCode();
        $success_codes = $endpoint['success']['status_codes'] ?? $this->config['success']['status_codes'];
        if(!in_array($status, $success_codes, true)) {
            $this->logRequestFailure($name, $method, $url, $status, $response->body());
            throw new \Exception('dns_request_failed', EQ_ERROR_UNKNOWN);
        }

        if($status === 204) {
            return [];
        }

        $data = $response->body();
        if(!is_array($data)) {
            return [];
        }

        if(isset($endpoint['response_path'])) {
            $data = $this->getPath($data, $endpoint['response_path']);
        }

        return is_array($data) ? $data : ['value' => $data];
    }

    protected function sendRequest(string $method, string $url, ?string $raw_body = null, array $options = []) {
        $request = new HttpRequest("{$method} {$url}");

        foreach($this->config['headers'] as $name => $value) {
            $request->header($name, $value);
        }

        foreach($options['headers'] ?? [] as $name => $value) {
            $request->header($name, $value);
        }

        $this->applyAuthentication($request, $method, $url, $raw_body ?? '', $options);

        if($raw_body !== null && $raw_body !== '') {
            $request->setBody($raw_body, true);
        }

        return $request->send();
    }

    protected function applyAuthentication(HttpRequest $request, string $method, string $url, string $raw_body, array $options = []): void {
        $auth = $options['auth'] ?? $this->config['auth'];
        $api_key = $options['api_key'] ?? $this->config['api_key'] ?? null;

        switch($auth['type'] ?? 'none') {
            case 'none':
                break;

            case 'header':
                if(empty($api_key)) {
                    throw new \Exception('missing_dns_api_key', EQ_ERROR_INVALID_CONFIG);
                }
                if(empty($auth['name'])) {
                    throw new \Exception('missing_dns_auth_header', EQ_ERROR_INVALID_CONFIG);
                }
                $request->header($auth['name'], $api_key);
                break;

            case 'bearer':
                if(empty($api_key)) {
                    throw new \Exception('missing_dns_api_key', EQ_ERROR_INVALID_CONFIG);
                }
                $request->header('Authorization', "Bearer {$api_key}");
                break;

            case 'basic':
                if(empty($api_key)) {
                    throw new \Exception('missing_dns_api_key', EQ_ERROR_INVALID_CONFIG);
                }
                $username = $auth['username'] ?? 'api';
                $request->header('Authorization', 'Basic ' . base64_encode("{$username}:{$api_key}"));
                break;

            default:
                throw new \Exception('unsupported_dns_auth_type', EQ_ERROR_INVALID_CONFIG);
        }
    }

    protected function buildUrl(string $path, array $query = []): string {
        $base_url = rtrim((string) ($this->config['base_url'] ?? ''), '/');
        if($base_url === '') {
            throw new \Exception('missing_dns_api_url', EQ_ERROR_INVALID_CONFIG);
        }

        $url = $base_url . '/' . ltrim($path, '/');
        if(count($query)) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    protected function encodeBody($body): ?string {
        if($body === null || $body === []) {
            return null;
        }

        if(is_string($body)) {
            return $body;
        }

        return json_encode($body, JSON_PRETTY_PRINT);
    }

    protected function resolveMap(array $mapping, array $context): array {
        $result = [];

        foreach($mapping as $key => $value) {
            $optional = false;
            $target_key = $key;

            if(is_string($key) && substr($key, 0, 1) === '?') {
                $optional = true;
                $target_key = substr($key, 1);
            }

            $resolved = $this->resolveValue($value, $context);
            if($optional && $this->isEmptyValue($resolved)) {
                continue;
            }

            if(is_string($target_key) && strpos($target_key, '.') !== false) {
                $this->setPath($result, $target_key, $resolved);
            }
            else {
                $result[$target_key] = $resolved;
            }
        }

        return $result;
    }

    protected function resolveValue($value, array $context) {
        if(is_array($value)) {
            $result = [];
            foreach($value as $key => $item) {
                $result[$key] = $this->resolveValue($item, $context);
            }
            return $result;
        }

        if(!is_string($value) || substr($value, 0, 1) !== '@') {
            return $value;
        }

        return $context[substr($value, 1)] ?? null;
    }

    protected function resolveTemplate(string $template, array $context, bool $url_encode = false): string {
        return preg_replace_callback(
            '/@([a-zA-Z0-9_]+)/',
            function($matches) use ($context, $url_encode) {
                $value = (string) ($context[$matches[1]] ?? '');
                return $url_encode ? rawurlencode($value) : $value;
            },
            $template
        );
    }

    protected function extractRecordId($record, bool $required = true) {
        if(is_scalar($record)) {
            return $record;
        }

        if(is_array($record)) {
            foreach(['id', 'record_id', 'provider_record_id'] as $field) {
                if(isset($record[$field]) && is_scalar($record[$field])) {
                    return $record[$field];
                }
            }
        }

        if($required) {
            throw new \Exception('missing_dns_record_id', EQ_ERROR_UNKNOWN);
        }

        return null;
    }

    protected function recordMatches(array $existing, array $record): bool {
        if(!$existing) {
            return false;
        }

        $existing_value = $existing['value'] ?? $existing['target'] ?? null;
        $existing_ttl = $existing['ttl'] ?? null;

        return $existing_value === $record['value']
            && (string) $existing_ttl === (string) $record['ttl'];
    }

    protected function formatRecordResult(string $status, array $record, $id, array $raw_response = []): array {
        return [
            'status'             => $status,
            'provider'           => $this->config['provider'] ?? null,
            'zone'               => $record['zone'],
            'name'               => $record['name'],
            'fqdn'               => $record['fqdn'],
            'type'               => $record['type'],
            'value'              => $record['value'],
            'ttl'                => $record['ttl'],
            'provider_record_id' => $id,
            'message'            => null,
            'raw_response'       => $raw_response
        ];
    }

    protected function getPath(array $data, string $path) {
        $cursor = $data;
        foreach(explode('.', $path) as $part) {
            if(!is_array($cursor) || !array_key_exists($part, $cursor)) {
                return null;
            }
            $cursor = $cursor[$part];
        }

        return $cursor;
    }

    protected function setPath(array &$target, string $path, $value): void {
        $parts = explode('.', $path);
        $cursor = &$target;

        foreach($parts as $part) {
            if(!isset($cursor[$part]) || !is_array($cursor[$part])) {
                $cursor[$part] = [];
            }
            $cursor = &$cursor[$part];
        }

        $cursor = $value;
    }

    protected function isEmptyValue($value): bool {
        return $value === null || $value === '' || $value === [];
    }

    protected function logRequestFailure(string $operation, string $method, string $url, int $status, $body): void {
        $details = $body;
        if(is_array($details)) {
            $details = json_encode($details, JSON_UNESCAPED_SLASHES);
        }

        if(!is_string($details)) {
            $details = '';
        }

        $details = trim($details);
        if(strlen($details) > 500) {
            $details = substr($details, 0, 500);
        }

        trigger_error(
            "PHP::DnsApi::{$operation}() failed: {$method} {$url} returned HTTP {$status}" . ($details === '' ? '' : " - {$details}"),
            EQ_REPORT_ERROR
        );
    }

    protected function mergeConfig(array $base, array $override): array {
        foreach($override as $key => $value) {
            if(is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeConfig($base[$key], $value);
            }
            else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
