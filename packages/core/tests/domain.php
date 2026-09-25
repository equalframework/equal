<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric Françoys
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Domain;

$tests = [

    '1001' => [
        'description'   => "Domain evaluation supports scalar operators without dynamic evaluation.",
        'act'           => function () {
            $conditions = [
                ["O'Reilly", '=', "O'Reilly", true],
                ['left', '<>', 'right', true],
                [4, '<', 5, true],
                [6, '>', 5, true],
                [5, '<=', 5, true],
                [5, '>=', 5, true],
                [2, 'in', [1, 2, 3], true],
                [4, 'not in', [1, 2, 3], true],
                [2, 'in', 2, true],
                [2, 'unknown', 2, false]
            ];

            foreach($conditions as [$operand, $operator, $value, $expected]) {
                $domain = new Domain(['field', $operator, $value]);
                if($domain->evaluate(['field' => $operand]) !== $expected) {
                    return false;
                }
            }

            return true;
        },
        'assert'        => fn($result) => $result === true
    ],

    '1002' => [
        'description'   => "Domain evaluation fails closed when a required field is missing.",
        'act'           => function () {
            $domain = new Domain([
                [
                    ['status', '=', 'active'],
                    ['score', '>=', 10]
                ],
                [
                    ['status', '=', 'pending'],
                    ['score', '>=', 20]
                ]
            ]);

            return [
                $domain->evaluate(['status' => 'active', 'score' => 10]),
                $domain->evaluate(['status' => 'pending', 'score' => 20]),
                $domain->evaluate(['status' => 'active']),
                $domain->evaluate(['status' => 'inactive', 'score' => 30])
            ];
        },
        'assert'        => fn($result) => $result === [true, true, false, false]
    ],

    '1003' => [
        'description'   => "Domain evaluation handles null, is not, and contains operators.",
        'act'           => function () {
            $is_empty = new Domain(['field', 'is', null]);
            $is_not_empty = new Domain(['field', 'is not', null]);
            $contains = new Domain(['field', 'contains', [3, 5]]);

            return [
                $is_empty->evaluate(['field' => null]),
                $is_empty->evaluate([]),
                $is_not_empty->evaluate(['field' => null]),
                $is_not_empty->evaluate(['field' => 'value']),
                $is_not_empty->evaluate([]),
                $contains->evaluate(['field' => [1, 3]]),
                $contains->evaluate(['field' => 5]),
                $contains->evaluate(['field' => [1, 2]]),
                $contains->evaluate(['field' => []])
            ];
        },
        'assert'        => fn($result) => $result === [true, false, false, true, false, true, true, false, false]
    ],

    '1004' => [
        'description'   => "Domain evaluation supports SQL wildcards, parsed object references, and integer timestamps.",
        'act'           => function () {
            $like = new Domain(['field', 'like', 'Al_ce%']);
            $ilike = new Domain(['field', 'ilike', 'al_ce%']);
            $contextual = new Domain(['organization_id', '=', 'object.organization_id']);
            $contextual->parse(['organization_id' => 7]);
            $date_reference = new Domain(['created', '>=', 'date.this.day']);
            $date_reference->parse();
            $resolved_date = $date_reference->toArray()[0][0][2];

            return [
                $like->evaluate(['field' => 'Alice Cooper']),
                $like->evaluate(['field' => 'alice Cooper']),
                $ilike->evaluate(['field' => 'alice Cooper']),
                $like->evaluate(['field' => ['Alice Cooper']]),
                $contextual->evaluate(['organization_id' => 7]),
                $contextual->evaluate(['organization_id' => 8]),
                is_int($resolved_date),
                $date_reference->evaluate(['created' => $resolved_date]),
                $date_reference->evaluate(['created' => $resolved_date - 1])
            ];
        },
        'assert'        => fn($result) => $result === [true, false, true, false, true, false, true, true, false]
    ]

];
