<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\dns;

class Dns {

    private string $context = '';

    private ?object $provider = null;

    private array $config = [];

    private array $providers = [];

    /**
     * Create a front DNS client that delegates to the concrete implementation.
     *
     *
     * Examples:
     *
     * ```
     * $dns = Dns::create('ovh', [
     *     'zones' => [
     *         'yb.run' => [
     *             'credentials' => [
     *                 'application_key'    => constant('DNS_API_APPLICATION_KEY'),
     *                 'application_secret' => constant('DNS_API_APPLICATION_SECRET'),
     *                 'consumer_key'       => constant('DNS_API_CONSUMER_KEY')
     *             ]
     *         ]
     *     ]
     * ]);
     *
     * $dns = Dns::create('dns', [
     *     'zones' => [
     *         'internal.example.com' => [
     *             'server'     => 'ns1.example.com',
     *             'key_name'   => 'equal-dns-update',
     *             'key_secret' => constant('DNS_TSIG_KEY_SECRET')
     *         ]
     *     ]
     * ]);
     * ```
     *
     * @param string|array $type ('dns', 'ovh', 'cloudflare', ...) or manager config
     * @param array $options
     * @return Dns
     */
    public static function create($type = 'dns', array $options = []): Dns {
        $self = new self();

        if(is_array($type)) {
            $self->context = $type['provider'] ?? $type['context'] ?? 'dns';
            $self->config = $type;
            return $self;
        }

        if(isset($options['zones'])) {
            $self->context = $type;
            $self->config = $options;
            return $self;
        }

        $self->context = $type;
        $self->provider = self::instantiate($type, $options);
        return $self;
    }

    /**
     * Create or update a DNS record and refresh the zone when supported.
     *
     * @param array $record
     * @param array $options
     * @return array
     */
    public function ensureRecord(array $record, array $options = []): array {
        return $this->getProviderForRecord($record)->ensureRecord($record, $options);
    }

    public function getRecords(string $zone, string $name, string $type = 'A', array $options = []): array {
        return $this->getProviderForZone($zone)->getRecords($zone, $name, $type, $options);
    }

    public function createRecord(array $record, array $options = []): array {
        return $this->getProviderForRecord($record)->createRecord($record, $options);
    }

    public function updateRecord($id, array $record, array $options = []): array {
        return $this->getProviderForRecord($record)->updateRecord($id, $record, $options);
    }

    public function deleteRecord($id, array $record, array $options = []): array {
        return $this->getProviderForRecord($record)->deleteRecord($id, $record, $options);
    }

    public function refreshZone(string $zone, array $options = []): array {
        return $this->getProviderForZone($zone)->refreshZone($zone, $options);
    }

    public function request(string $method, string $path, $body = null, array $options = []): array {
        return $this->getProvider()->request($method, $path, $body, $options);
    }

    private function getProvider(): object {
        if(!$this->provider) {
            throw new \Exception('dns_provider_not_created', EQ_ERROR_INVALID_CONFIG);
        }

        return $this->provider;
    }

    private function getProviderForRecord(array $record): object {
        if(!isset($record['zone'])) {
            throw new \Exception('missing_dns_zone', EQ_ERROR_MISSING_PARAM);
        }

        return $this->getProviderForZone($record['zone']);
    }

    private function getProviderForZone(string $zone): object {
        if(empty($this->config['zones'])) {
            return $this->getProvider();
        }

        $zone = DnsRecordHelper::normalizeZone($zone);
        $zone_config = $this->config['zones'][$zone] ?? null;
        if(!$zone_config) {
            throw new \Exception('dns_zone_not_allowed', EQ_ERROR_NOT_ALLOWED);
        }

        $provider_type = $this->resolveProviderType($zone_config);
        if(!$provider_type) {
            throw new \Exception('missing_dns_provider', EQ_ERROR_INVALID_CONFIG);
        }

        if(!isset($this->providers[$zone])) {
            $provider_config = [];

            if(isset($this->config['providers'][$provider_type]) && is_array($this->config['providers'][$provider_type])) {
                $provider_config = array_replace_recursive($provider_config, $this->config['providers'][$provider_type]);
            }

            if(isset($zone_config['credentials']) && is_array($zone_config['credentials'])) {
                $provider_config = array_replace_recursive($provider_config, $zone_config['credentials']);
            }

            $provider_config = array_replace_recursive($provider_config, $zone_config);
            unset($provider_config['provider'], $provider_config['credentials']);
            $provider_config['allowed_zones'] = [$zone];

            $this->providers[$zone] = self::instantiate($provider_type, $provider_config);
        }

        return $this->providers[$zone];
    }

    private function resolveProviderType(array $zone_config): ?string {
        switch($this->context) {
            case 'dns':
                return 'rfc2136';
        }

        return $zone_config['provider'] ?? ($this->context ?: null);
    }

    private static function instantiate(string $type, array $options): object {
        switch($type) {
            case 'ovh':
                return new api\DnsApiOvh($options);

            case 'dns':
            case 'rfc2136':
                return new DnsRfc($options);
        }

        throw new \Exception('unsupported_dns_provider', EQ_ERROR_INVALID_CONFIG);
    }
}
