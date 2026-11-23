<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\test\Test;

$tests = [

    '5201' => [
            'description' => 'ORM transaction: rollback restores the previous object value.',
            'arrange'     => function() {
                    $object = Test::create([
                            'string_short' => 'tx-before'
                        ])
                        ->read(['id'])
                        ->first();

                    return [
                        'id'      => $object['id'],
                        'initial' => 'tx-before',
                        'changed' => 'tx-rback'
                    ];
                },
            'act'         => function($fixture) {
                    ['db' => $db] = eQual::inject(['db']);

                    $read_value = function() use($fixture) {
                        foreach([$fixture['initial'], $fixture['changed']] as $value) {
                            $object = Test::search([
                                    ['id', '=', $fixture['id']],
                                    ['string_short', '=', $value]
                                ])
                                ->read(['id'])
                                ->first();

                            if(($object['id'] ?? 0) === $fixture['id']) {
                                return $value;
                            }
                        }

                        return null;
                    };

                    try {
                        $before = $read_value();

                        $db->beginTransaction();
                        Test::id($fixture['id'])->write([
                            'string_short' => $fixture['changed']
                        ]);
                        $during = $read_value();
                        $db->rollbackTransaction();

                        $after = $read_value();

                        return $fixture + [
                            'before' => $before,
                            'during' => $during,
                            'after'  => $after
                        ];
                    }
                    catch(\Throwable $e) {
                        if($db->inTransaction()) {
                            $db->rollbackTransaction();
                        }
                        Test::id($fixture['id'])->delete(true);
                        throw $e;
                    }
                },
            'assert'      => function($result) {
                    return ($result['before'] ?? null) === ($result['initial'] ?? null)
                        && ($result['during'] ?? null) === ($result['changed'] ?? null)
                        && ($result['after'] ?? null) === ($result['initial'] ?? null);
                },
            'rollback'    => function($result) {
                    if(($result['id'] ?? 0) > 0) {
                        Test::id($result['id'])->delete(true);
                    }
                }
        ],

    '5202' => [
            'description' => 'ORM transaction: commit persists the new object value.',
            'arrange'     => function() {
                    $object = Test::create([
                            'string_short' => 'tx-before'
                        ])
                        ->read(['id'])
                        ->first();

                    return [
                        'id'      => $object['id'],
                        'initial' => 'tx-before',
                        'changed' => 'tx-commit'
                    ];
                },
            'act'         => function($fixture) {
                    ['db' => $db] = eQual::inject(['db']);

                    $read_value = function() use($fixture) {
                        foreach([$fixture['initial'], $fixture['changed']] as $value) {
                            $object = Test::search([
                                    ['id', '=', $fixture['id']],
                                    ['string_short', '=', $value]
                                ])
                                ->read(['id'])
                                ->first();

                            if(($object['id'] ?? 0) === $fixture['id']) {
                                return $value;
                            }
                        }

                        return null;
                    };

                    try {
                        $before = $read_value();

                        $db->beginTransaction();
                        Test::id($fixture['id'])->write([
                            'string_short' => $fixture['changed']
                        ]);
                        $during = $read_value();
                        $db->commitTransaction();

                        $after = $read_value();

                        return $fixture + [
                            'before' => $before,
                            'during' => $during,
                            'after'  => $after
                        ];
                    }
                    catch(\Throwable $e) {
                        if($db->inTransaction()) {
                            $db->rollbackTransaction();
                        }
                        Test::id($fixture['id'])->delete(true);
                        throw $e;
                    }
                },
            'assert'      => function($result) {
                    return ($result['before'] ?? null) === ($result['initial'] ?? null)
                        && ($result['during'] ?? null) === ($result['changed'] ?? null)
                        && ($result['after'] ?? null) === ($result['changed'] ?? null);
                },
            'rollback'    => function($result) {
                    if(($result['id'] ?? 0) > 0) {
                        Test::id($result['id'])->delete(true);
                    }
                }
        ]
];
