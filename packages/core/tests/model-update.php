<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use core\test\Test;

$tests = [

    '5101' => [
            'description' => 'Model update: two saves based on the same draft snapshot preserve both objects.',
            'arrange'     => function() {
                    $draft = Test::create([
                            'state'        => 'draft',
                            'string_short' => 'initial'
                        ])
                        ->read(['id', 'state', 'modified'])
                        ->first();

                    return [
                        'draft_id' => $draft['id'],
                        'state'    => $draft['state'],
                        'modified' => $draft['modified']
                    ];
                },
            'act'         => function($fixtures) {
                    $snapshot = [
                        'entity'            => Test::class,
                        'ids'               => [$fixtures['draft_id']],
                        'previous_state'     => $fixtures['state'],
                        'previous_modified'  => date('c', $fixtures['modified'])
                    ];

                    $first = eQual::run('do', 'core_model_update', $snapshot + [
                        'fields' => ['string_short' => 'first']
                    ]);
                    $second = eQual::run('do', 'core_model_update', $snapshot + [
                        'fields' => ['string_short' => 'second']
                    ]);

                    $first_id = $first[0]['id'] ?? 0;
                    $second_id = $second[0]['id'] ?? 0;

                    $first_object = Test::id($first_id)
                        ->read(['state', 'string_short'])
                        ->first();
                    $second_object = Test::id($second_id)
                        ->read(['state', 'string_short'])
                        ->first();

                    return $fixtures + [
                        'first_id'     => $first_id,
                        'second_id'    => $second_id,
                        'first_object' => $first_object,
                        'second_object' => $second_object
                    ];
                },
            'assert'      => function($result) {
                    return ($result['first_id'] ?? 0) === ($result['draft_id'] ?? 0)
                        && ($result['second_id'] ?? 0) > 0
                        && $result['second_id'] !== $result['first_id']
                        && ($result['first_object']['state'] ?? null) === 'instance'
                        && ($result['first_object']['string_short'] ?? null) === 'first'
                        && ($result['second_object']['state'] ?? null) === 'instance'
                        && ($result['second_object']['string_short'] ?? null) === 'second';
                },
            'rollback'    => function($result) {
                    $ids = array_values(array_unique(array_filter([
                        $result['draft_id'] ?? 0,
                        $result['first_id'] ?? 0,
                        $result['second_id'] ?? 0
                    ])));

                    if(count($ids)) {
                        Test::ids($ids)->delete(true);
                    }
                }
        ]
];
