<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\dns;

class DnsRfc implements DnsProviderInterface {

    private array $config = [
        'server'          => null,
        'port'            => 53,
        'key_name'        => null,
        'key_secret'      => null,
        'algorithm'       => 'hmac-sha256',
        'nsupdate_bin'    => 'nsupdate',
        'allowed_zones'   => [],
        'supported_types' => ['A'],
        'min_ttl'         => 60,
        'default_ttl'     => 3600
    ];

    public function __construct(array $config = []) {
        $this->config = array_replace_recursive($this->config, $config);
    }

    public function ensureRecord(array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        $records = $this->getRecords($record['zone'], $record['name'], $record['type'], $options);

        if(count($records) > 1) {
            throw new \Exception('dns_record_conflict', EQ_ERROR_CONFLICT_OBJECT);
        }

        if(count($records) === 1 && $this->recordMatches($records[0], $record)) {
            return $this->formatRecordResult('unchanged', $record, $records[0]);
        }

        $status = count($records) === 1 ? 'updated' : 'created';
        $this->replaceRecord($record);

        return $this->formatRecordResult($status, $record);
    }

    public function getRecords(string $zone, string $name, string $type = 'A', array $options = []): array {
        $criteria = DnsRecordHelper::normalizeCriteria([
            'zone' => $zone,
            'name' => $name,
            'type' => $type
        ], [
            'allowed_zones'   => $options['allowed_zones'] ?? $this->config['allowed_zones'],
            'supported_types' => $options['supported_types'] ?? $this->config['supported_types']
        ]);

        $dns_type = $this->getDnsGetRecordType($criteria['type']);
        if($dns_type === null || !function_exists('dns_get_record')) {
            return [];
        }

        $records = @dns_get_record($criteria['fqdn'], $dns_type);
        if(!is_array($records)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn($item) => $this->normalizeDnsRecord($criteria, $item),
            $records
        )));
    }

    public function supports(string $type): bool {
        $type = strtoupper(trim($type));
        $supported_types = DnsRecordHelper::normalizeStringList($this->config['supported_types'], true);

        return in_array($type, $supported_types, true);
    }

    public function createRecord(array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        $this->addRecord($record);

        return $this->formatRecordResult('created', $record);
    }

    public function updateRecord($id, array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        $this->replaceRecord($record);

        return $this->formatRecordResult('updated', $record);
    }

    public function deleteRecord($id, array $record, array $options = []): array {
        $record = $this->normalizeRecord($record, $options);
        $this->deleteRecordSet($record);

        return $this->formatRecordResult('deleted', $record);
    }

    public function refreshZone(string $zone, array $options = []): array {
        return [];
    }

    private function normalizeRecord(array $record, array $options = []): array {
        return DnsRecordHelper::normalizeRecord($record, [
            'allowed_zones'   => $options['allowed_zones'] ?? $this->config['allowed_zones'],
            'supported_types' => $options['supported_types'] ?? $this->config['supported_types'],
            'min_ttl'         => $options['min_ttl'] ?? $this->config['min_ttl'],
            'default_ttl'     => $options['default_ttl'] ?? $this->config['default_ttl']
        ]);
    }

    private function replaceRecord(array $record): void {
        $this->runUpdate($record['zone'], [
            $this->buildDeleteCommand($record),
            $this->buildAddCommand($record)
        ]);
    }

    private function addRecord(array $record): void {
        $this->runUpdate($record['zone'], [
            $this->buildAddCommand($record)
        ]);
    }

    private function deleteRecordSet(array $record): void {
        $this->runUpdate($record['zone'], [
            $this->buildDeleteCommand($record)
        ]);
    }

    private function runUpdate(string $zone, array $commands): void {
        $this->validateConfig($zone);

        $key_file = tempnam(sys_get_temp_dir(), 'equal_dns_tsig_');
        $script_file = tempnam(sys_get_temp_dir(), 'equal_dns_update_');
        if(!$key_file || !$script_file) {
            throw new \Exception('dns_update_failed', EQ_ERROR_UNKNOWN);
        }

        try {
            $key_content = 'key "' . $this->escapeKeyName($this->config['key_name']) . "\" {\n"
                . '    algorithm ' . $this->config['algorithm'] . ";\n"
                . '    secret "' . $this->escapeKeySecret($this->config['key_secret']) . "\";\n"
                . "};\n";

            $script_content = "server {$this->config['server']} {$this->config['port']}\n"
                . "zone {$zone}\n"
                . implode("\n", $commands)
                . "\nsend\n";

            file_put_contents($key_file, $key_content);
            file_put_contents($script_file, $script_content);
            @chmod($key_file, 0600);
            @chmod($script_file, 0600);

            $cmd = escapeshellarg($this->config['nsupdate_bin'])
                . ' -k '
                . escapeshellarg($key_file)
                . ' '
                . escapeshellarg($script_file);

            $output = [];
            $result_code = 0;
            exec($cmd . ' 2>&1', $output, $result_code);

            if($result_code !== 0) {
                trigger_error(
                    'PHP::DnsRfc::runUpdate() failed: ' . implode("\n", $output),
                    EQ_REPORT_ERROR
                );
                throw new \Exception('dns_update_failed', EQ_ERROR_UNKNOWN);
            }
        }
        finally {
            if(is_string($key_file) && file_exists($key_file)) {
                unlink($key_file);
            }
            if(is_string($script_file) && file_exists($script_file)) {
                unlink($script_file);
            }
        }
    }

    private function validateConfig(string $zone): void {
        foreach(['server', 'key_name', 'key_secret', 'algorithm', 'nsupdate_bin'] as $key) {
            if(empty($this->config[$key])) {
                throw new \Exception('missing_dns_update_config', EQ_ERROR_INVALID_CONFIG);
            }
            if(strpbrk((string) $this->config[$key], "\r\n") !== false) {
                throw new \Exception('invalid_dns_update_config', EQ_ERROR_INVALID_CONFIG);
            }
        }

        if(!filter_var($this->config['port'], FILTER_VALIDATE_INT)) {
            throw new \Exception('invalid_dns_update_config', EQ_ERROR_INVALID_CONFIG);
        }

        if(strpbrk($zone, "\r\n") !== false) {
            throw new \Exception('invalid_dns_update_config', EQ_ERROR_INVALID_CONFIG);
        }
    }

    private function buildDeleteCommand(array $record): string {
        return 'update delete ' . $this->absoluteName($record['fqdn']) . ' ' . $record['type'];
    }

    private function buildAddCommand(array $record): string {
        return 'update add '
            . $this->absoluteName($record['fqdn'])
            . ' '
            . $record['ttl']
            . ' '
            . $record['type']
            . ' '
            . $this->formatValue($record);
    }

    private function formatValue(array $record): string {
        switch($record['type']) {
            case 'TXT':
                return '"' . addcslashes($record['value'], "\\\"") . '"';
        }

        return $record['value'];
    }

    private function absoluteName(string $fqdn): string {
        return rtrim($fqdn, '.') . '.';
    }

    private function escapeKeyName(string $value): string {
        return addcslashes($value, "\\\"");
    }

    private function escapeKeySecret(string $value): string {
        return addcslashes($value, "\\\"");
    }

    private function getDnsGetRecordType(string $type): ?int {
        switch($type) {
            case 'A':
                return DNS_A;
            case 'AAAA':
                return defined('DNS_AAAA') ? DNS_AAAA : null;
            case 'CNAME':
                return DNS_CNAME;
            case 'TXT':
                return DNS_TXT;
            case 'MX':
                return DNS_MX;
        }

        return null;
    }

    private function normalizeDnsRecord(array $criteria, array $item): ?array {
        $value = null;
        switch($criteria['type']) {
            case 'A':
                $value = $item['ip'] ?? null;
                break;
            case 'AAAA':
                $value = $item['ipv6'] ?? null;
                break;
            case 'CNAME':
            case 'MX':
                $value = isset($item['target']) ? rtrim($item['target'], '.') : null;
                break;
            case 'TXT':
                $value = $item['txt'] ?? null;
                break;
        }

        if($value === null) {
            return null;
        }

        return [
            'zone'  => $criteria['zone'],
            'name'  => $criteria['name'],
            'fqdn'  => $criteria['fqdn'],
            'type'  => $criteria['type'],
            'value' => $value,
            'ttl'   => $item['ttl'] ?? null,
            'raw'   => $item
        ];
    }

    private function recordMatches(array $existing, array $record): bool {
        return ($existing['value'] ?? null) === $record['value'];
    }

    private function formatRecordResult(string $status, array $record, array $raw_response = []): array {
        return [
            'status'             => $status,
            'provider'           => 'dns',
            'zone'               => $record['zone'],
            'name'               => $record['name'],
            'fqdn'               => $record['fqdn'],
            'type'               => $record['type'],
            'value'              => $record['value'],
            'ttl'                => $record['ttl'],
            'provider_record_id' => null,
            'message'            => null,
            'raw_response'       => $raw_response
        ];
    }
}
