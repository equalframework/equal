<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\dns;

class DnsRecordHelper {

    public const DEFAULT_SUPPORTED_TYPES = ['A'];

    public const DEFAULT_MIN_TTL = 60;

    /**
     * @param array{
     *     zone?: string,
     *     name?: string,
     *     subdomain?: string,
     *     type?: string,
     *     value?: string,
     *     target?: string,
     *     ttl?: int|string
     * } $record
     * @param array{
     *     allowed_zones?: string[]|string,
     *     supported_types?: string[],
     *     min_ttl?: int
     * } $options
     * @return array{zone: string, name: string, subdomain: string, fqdn: string, type: string, value: string, target: string, ttl: int, priority: mixed, options: array}
     */
    public static function normalizeRecord(array $record, array $options = []): array {
        $allowed_zones = self::normalizeStringList($options['allowed_zones'] ?? []);
        $supported_types = self::normalizeStringList($options['supported_types'] ?? self::DEFAULT_SUPPORTED_TYPES, true);
        $min_ttl = (int) ($options['min_ttl'] ?? self::DEFAULT_MIN_TTL);
        $default_ttl = (int) ($options['default_ttl'] ?? $record['ttl'] ?? 3600);

        $zone = self::normalizeZone($record['zone'] ?? '');
        if($zone === '') {
            throw new \Exception('missing_dns_zone', EQ_ERROR_MISSING_PARAM);
        }

        if(count($allowed_zones) && !in_array($zone, $allowed_zones, true)) {
            throw new \Exception('dns_zone_not_allowed', EQ_ERROR_NOT_ALLOWED);
        }

        $type = strtoupper(trim((string) ($record['type'] ?? 'A')));
        if($type === '') {
            throw new \Exception('missing_dns_record_type', EQ_ERROR_MISSING_PARAM);
        }

        if(!in_array($type, $supported_types, true)) {
            throw new \Exception('unsupported_dns_record_type', EQ_ERROR_INVALID_PARAM);
        }

        if(!array_key_exists('name', $record) && !array_key_exists('subdomain', $record)) {
            throw new \Exception('missing_dns_record_name', EQ_ERROR_MISSING_PARAM);
        }

        $name = self::normalizeSubdomain($record['name'] ?? $record['subdomain'] ?? '', $zone);
        self::assertValidName($name);

        $value = trim((string) ($record['value'] ?? $record['target'] ?? ''));
        if($value === '') {
            throw new \Exception('missing_dns_record_value', EQ_ERROR_MISSING_PARAM);
        }
        self::assertValidValue($type, $value);

        if(!isset($record['ttl']) || $record['ttl'] === '') {
            $record['ttl'] = $default_ttl;
        }

        if(filter_var($record['ttl'], FILTER_VALIDATE_INT) === false) {
            throw new \Exception('invalid_dns_record_ttl', EQ_ERROR_INVALID_PARAM);
        }

        $ttl = (int) $record['ttl'];
        if($ttl < $min_ttl) {
            throw new \Exception('invalid_dns_record_ttl', EQ_ERROR_INVALID_PARAM);
        }

        return [
            'zone'      => $zone,
            'name'      => $name,
            'subdomain' => $name,
            'fqdn'      => self::buildFqdn($zone, $name),
            'type'      => $type,
            'value'     => $value,
            'target'    => $value,
            'ttl'       => $ttl,
            'priority'  => $record['priority'] ?? null,
            'options'   => $record['options'] ?? []
        ];
    }

    /**
     * @param array{
     *     zone?: string,
     *     name?: string,
     *     subdomain?: string,
     *     type?: string
     * } $criteria
     * @param array{
     *     allowed_zones?: string[]|string,
     *     supported_types?: string[]
     * } $options
     * @return array{zone: string, name: string, subdomain: string, fqdn: string, type: string}
     */
    public static function normalizeCriteria(array $criteria, array $options = []): array {
        $allowed_zones = self::normalizeStringList($options['allowed_zones'] ?? []);
        $supported_types = self::normalizeStringList($options['supported_types'] ?? self::DEFAULT_SUPPORTED_TYPES, true);

        $zone = self::normalizeZone($criteria['zone'] ?? '');
        if($zone === '') {
            throw new \Exception('missing_dns_zone', EQ_ERROR_MISSING_PARAM);
        }

        if(count($allowed_zones) && !in_array($zone, $allowed_zones, true)) {
            throw new \Exception('dns_zone_not_allowed', EQ_ERROR_NOT_ALLOWED);
        }

        $type = strtoupper(trim((string) ($criteria['type'] ?? 'A')));
        if($type === '') {
            throw new \Exception('missing_dns_record_type', EQ_ERROR_MISSING_PARAM);
        }

        if(!in_array($type, $supported_types, true)) {
            throw new \Exception('unsupported_dns_record_type', EQ_ERROR_INVALID_PARAM);
        }

        if(!array_key_exists('name', $criteria) && !array_key_exists('subdomain', $criteria)) {
            throw new \Exception('missing_dns_record_name', EQ_ERROR_MISSING_PARAM);
        }

        $name = self::normalizeSubdomain($criteria['name'] ?? $criteria['subdomain'] ?? '', $zone);
        self::assertValidName($name);

        return [
            'zone'      => $zone,
            'name'      => $name,
            'subdomain' => $name,
            'fqdn'      => self::buildFqdn($zone, $name),
            'type'      => $type
        ];
    }

    public static function normalizeZone(string $zone): string {
        return strtolower(rtrim(trim($zone), '.'));
    }

    public static function normalizeSubdomain(string $subdomain, string $zone = ''): string {
        $subdomain = strtolower(rtrim(trim($subdomain), '.'));

        if($subdomain === '@') {
            return '';
        }

        $zone = self::normalizeZone($zone);
        if($zone !== '' && $subdomain === $zone) {
            return '';
        }

        if($zone !== '' && substr($subdomain, -(strlen($zone) + 1)) === ".{$zone}") {
            $subdomain = substr($subdomain, 0, -(strlen($zone) + 1));
        }

        return $subdomain;
    }

    public static function buildFqdn(string $zone, string $name): string {
        $zone = self::normalizeZone($zone);
        $name = self::normalizeSubdomain($name, $zone);

        return $name === '' ? $zone : "{$name}.{$zone}";
    }

    /**
     * @param string[]|string $value
     * @return string[]
     */
    public static function normalizeStringList($value, bool $uppercase = false): array {
        if(is_string($value)) {
            $value = preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        $result = [];
        foreach($value as $item) {
            $item = trim((string) $item);
            if($item === '') {
                continue;
            }
            $result[] = $uppercase ? strtoupper($item) : strtolower(rtrim($item, '.'));
        }

        return array_values(array_unique($result));
    }

    private static function assertValidName(string $name): void {
        if($name === '') {
            return;
        }

        if(strlen($name) > 253) {
            throw new \Exception('invalid_dns_record_name', EQ_ERROR_INVALID_PARAM);
        }

        $labels = explode('.', $name);
        foreach($labels as $label) {
            if($label === '' || strlen($label) > 63) {
                throw new \Exception('invalid_dns_record_name', EQ_ERROR_INVALID_PARAM);
            }

            if(!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $label)) {
                throw new \Exception('invalid_dns_record_name', EQ_ERROR_INVALID_PARAM);
            }
        }
    }

    private static function assertValidValue(string $type, string $value): void {
        switch($type) {
            case 'A':
                if(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
                    throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
                }
                break;

            case 'AAAA':
                if(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
                    throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
                }
                break;

            case 'CNAME':
                self::assertValidHostnameValue($value);
                break;

            case 'TXT':
                if(strlen($value) > 255) {
                    throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
                }
                break;

            case 'MX':
                if(!preg_match('/^\d+\s+[a-z0-9][a-z0-9.-]*[a-z0-9]\.?$/i', $value)) {
                    throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
                }
                break;
        }
    }

    private static function assertValidHostnameValue(string $value): void {
        $value = rtrim(strtolower($value), '.');

        if($value === '' || strlen($value) > 253) {
            throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
        }

        foreach(explode('.', $value) as $label) {
            if($label === '' || strlen($label) > 63) {
                throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
            }

            if(!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $label)) {
                throw new \Exception('invalid_dns_record_value', EQ_ERROR_INVALID_PARAM);
            }
        }
    }
}
