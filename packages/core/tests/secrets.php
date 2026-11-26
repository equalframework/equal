<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\security\Credential;
use core\security\CredentialType;

global $test;

$tests = [

    '6101' => [
            'description' => 'Secret usage: ORM values are encrypted in storage and decrypted when loaded.',
            'arrange'     => function () {
                    if(
                        !function_exists('sodium_crypto_secretbox')
                        || !defined('CIPHER_KEY')
                        || strlen((string) constant('CIPHER_KEY')) === 0
                    ) {
                        return [
                            'id'          => 0,
                            'unavailable' => true
                        ];
                    }

                    $credential_type = CredentialType::create([
                            'code'            => 'secret_usage_integration_test_6101',
                            'description'     => 'Credential type used by the secret usage integration test.',
                            'secret_template' => 'credential-secret-integration-value'
                        ])
                        ->read(['id'])
                        ->first();

                    $credential = Credential::create([
                            'name'               => 'Secret usage integration test',
                            'credential_type_id' => $credential_type['id']
                        ])
                        ->read(['id'])
                        ->first();

                    return [
                        'id'                 => $credential['id'] ?? 0,
                        'credential_type_id' => $credential_type['id'] ?? 0,
                        'unavailable'        => false
                    ];
                },
            'act'         => function ($fixtures) {
                    if($fixtures['unavailable']) {
                        return $fixtures;
                    }

                    ['orm' => $orm, 'db' => $db] = eQual::inject(['orm', 'db']);
                    $stored_records = $db->getRecords(
                        Credential::getModelTable(),
                        ['secret'],
                        [$fixtures['id']]
                    );
                    $stored_record = $db->fetchArray($stored_records);
                    $loaded_records = $orm->read(
                        Credential::class,
                        [$fixtures['id']],
                        ['secret']
                    );
                    $stored_value = $stored_record['secret'] ?? null;
                    $loaded_value = $loaded_records[$fixtures['id']]['secret'] ?? null;

                    return $fixtures + [
                        'stored_is_encrypted' => is_string($stored_value)
                            && strpos($stored_value, 'eqsecret:v1:') === 0
                            && strpos($stored_value, 'credential-secret-integration-value') === false,
                        'loaded_is_clear' => $loaded_value === 'credential-secret-integration-value'
                    ];
                },
            'assert'      => function ($result) {
                    return !$result['unavailable']
                        && ($result['id'] ?? 0) > 0
                        && ($result['stored_is_encrypted'] ?? false)
                        && ($result['loaded_is_clear'] ?? false);
                },
            'rollback'    => function ($result) {
                    $id = $result['id'] ?? 0;
                    if($id > 0) {
                        Credential::id($id)->delete(true);
                    }
                    $credential_type_id = $result['credential_type_id'] ?? 0;
                    if($credential_type_id > 0) {
                        CredentialType::id($credential_type_id)->delete(true);
                    }
                }
        ],

    '6102' => [
            'description' => 'Secret usage: identical clear values produce different encrypted storage payloads.',
            'arrange'     => function () {
                    if(
                        !function_exists('sodium_crypto_secretbox')
                        || !defined('CIPHER_KEY')
                        || strlen((string) constant('CIPHER_KEY')) === 0
                    ) {
                        return [
                            'ids'         => [],
                            'unavailable' => true
                        ];
                    }

                    $credential_type = CredentialType::create([
                            'code'            => 'secret_usage_integration_test_6102',
                            'description'     => 'Credential type used by the secret usage integration test.',
                            'secret_template' => 'unused-secret-template'
                        ])
                        ->read(['id'])
                        ->first();

                    $ids = [];
                    foreach(['A', 'B'] as $suffix) {
                        $credential = Credential::create([
                                'name'               => "Secret usage integration test {$suffix}",
                                'credential_type_id' => $credential_type['id'],
                                'secret'             => 'repeated-secret-integration-value'
                            ])
                            ->read(['id'])
                            ->first();
                        $ids[] = $credential['id'] ?? 0;
                    }

                    return [
                        'ids'                => $ids,
                        'credential_type_id' => $credential_type['id'] ?? 0,
                        'unavailable'       => false
                    ];
                },
            'act'         => function ($fixtures) {
                    if($fixtures['unavailable']) {
                        return $fixtures;
                    }

                    ['db' => $db] = eQual::inject(['db']);
                    $stored_records = $db->getRecords(
                        Credential::getModelTable(),
                        ['id', 'secret'],
                        $fixtures['ids']
                    );
                    $stored_values = [];
                    while($stored_record = $db->fetchArray($stored_records)) {
                        $stored_values[$stored_record['id']] = $stored_record['secret'];
                    }

                    $first_value = $stored_values[$fixtures['ids'][0]] ?? null;
                    $second_value = $stored_values[$fixtures['ids'][1]] ?? null;

                    return $fixtures + [
                        'both_are_encrypted' => is_string($first_value)
                            && is_string($second_value)
                            && strpos($first_value, 'eqsecret:v1:') === 0
                            && strpos($second_value, 'eqsecret:v1:') === 0
                            && strpos($first_value, 'repeated-secret-integration-value') === false
                            && strpos($second_value, 'repeated-secret-integration-value') === false,
                        'payloads_are_distinct' => $first_value !== $second_value
                    ];
                },
            'assert'      => function ($result) {
                    $ids = $result['ids'] ?? [];
                    if(
                        $result['unavailable']
                        || count($ids) !== 2
                    ) {
                        return false;
                    }

                    return ($result['both_are_encrypted'] ?? false)
                        && ($result['payloads_are_distinct'] ?? false);
                },
            'rollback'    => function ($result) {
                    $ids = array_filter($result['ids'] ?? [], fn($id) => $id > 0);
                    if(!empty($ids)) {
                        Credential::ids($ids)->delete(true);
                    }
                    $credential_type_id = $result['credential_type_id'] ?? 0;
                    if($credential_type_id > 0) {
                        CredentialType::id($credential_type_id)->delete(true);
                    }
                }
        ]
];
