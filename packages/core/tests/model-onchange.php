<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\tests\fixtures\model_onchange {

    class OnchangeProbe extends \core\test\Test
    {
        public static function getColumns() {
            return [
                'onchange_mode' => [
                    'type' => 'string'
                ],
                'integer_value' => [
                    'type' => 'integer'
                ],
                'boolean_value' => [
                    'type' => 'boolean'
                ],
                'test1_id' => [
                    'type'           => 'many2one',
                    'foreign_object' => 'core\test\Test1'
                ]
            ];
        }

        public static function onchange($event) {
            $mode = $event['onchange_mode'] ?? null;

            if($mode === 'direct_invalid') {
                return ['string_short' => '1234567890'];
            }

            if($mode === 'descriptor_invalid') {
                return [
                    'string_short' => [
                        'value'     => '1234567890',
                        'selection' => ['1234567890'],
                        'readonly'  => true,
                        'errors'    => (object) ['existing_error' => 'Existing error.']
                    ]
                ];
            }

            if($mode === 'selection_only') {
                return [
                    'string_short' => [
                        'selection' => ['first', 'second']
                    ]
                ];
            }

            if($mode === 'descriptor_datetime') {
                return [
                    'datetime' => [
                        'value'    => 1711990542,
                        'readonly' => true
                    ]
                ];
            }

            if($mode === 'empty_optional_string') {
                return ['string_short' => ''];
            }

            if($mode === 'descriptor_null') {
                return [
                    'string_short' => [
                        'value'    => null,
                        'visible'  => true,
                        'required' => false
                    ]
                ];
            }

            if($mode === 'zero_and_false') {
                return [
                    'integer_value' => 0,
                    'boolean_value' => false
                ];
            }

            if($mode === 'direct_array') {
                return ['tests1_m2m_ids' => [1, 2]];
            }

            if($mode === 'descriptor_array') {
                return [
                    'tests1_m2m_ids' => [
                        'value'     => [1, 2],
                        'domain'    => [['id', '>', 0]],
                        'selection' => [1, 2, 3],
                        'visible'   => true,
                        'readonly'  => false,
                        'required'  => true
                    ]
                ];
            }

            if($mode === 'many2one_array') {
                return [
                    'test1_id' => [
                        'id'   => 7,
                        'name' => 'Related object'
                    ]
                ];
            }

            return [];
        }
    }

    class OnchangeUserProbe extends \core\User
    {
        public static function getModelScope(): ?string {
            return null;
        }

        public static function onchange($event, $values) {
            return array_key_exists('login', $event)
                ? ['login' => $event['login']]
                : [];
        }
    }

    class ParentOnchangeProbe extends \core\test\Test
    {
        public static function getColumns() {
            return [
                'onchange_mode' => [
                    'type' => 'string'
                ]
            ];
        }

        public static function onchange($event) {
            return ($event['onchange_mode'] ?? null) === 'inherited_invalid'
                ? ['string_short' => 'inherited']
                : [];
        }
    }

    class ChildOnchangeProbe extends ParentOnchangeProbe
    {
        public static function getConstraints() {
            return [
                'string_short' => [
                    'child_constraint' => [
                        'message'  => 'Value rejected by child entity.',
                        'function' => static fn() => false
                    ]
                ]
            ];
        }
    }

    class ContextConstraintProbe extends \core\test\Test
    {
        public static function getColumns() {
            return [
                'onchange_mode' => [
                    'type' => 'string'
                ]
            ];
        }

        public static function getConstraints() {
            return [
                'string_short' => [
                    'currency_mismatch' => [
                        'message'  => 'Currency does not match returned values.',
                        'function' => static fn($value, $values) => ($values['string_currency'] ?? null) === 'EUR'
                    ]
                ]
            ];
        }

        public static function onchange($event) {
            return ($event['onchange_mode'] ?? null) === 'context_invalid'
                ? ['string_short' => 'valid', 'string_currency' => 'USD']
                : [];
        }
    }
}

namespace {

    use core\tests\fixtures\model_onchange\ChildOnchangeProbe;
    use core\tests\fixtures\model_onchange\ContextConstraintProbe;
    use core\tests\fixtures\model_onchange\OnchangeProbe;
    use core\tests\fixtures\model_onchange\OnchangeUserProbe;
    use core\User;

    $tests = [

        '5201' => [
            'description' => 'Model onchange: a direct invalid value is wrapped with ORM validation errors.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'direct_invalid']
                ]);
            },
            'assert'      => function($result) {
                $errors = (array) ($result['string_short']['errors'] ?? []);

                return ($result['string_short']['value'] ?? null) === '1234567890'
                    && isset($errors['size_exceeded']);
            }
        ],

        '5202' => [
            'description' => 'Model onchange: validation errors are merged into an existing UI descriptor.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'descriptor_invalid']
                ]);
            },
            'assert'      => function($result) {
                $field = $result['string_short'] ?? [];
                $errors = (array) ($field['errors'] ?? []);

                return ($field['value'] ?? null) === '1234567890'
                    && ($field['selection'] ?? null) === ['1234567890']
                    && ($field['readonly'] ?? null) === true
                    && isset($errors['existing_error'])
                    && isset($errors['size_exceeded']);
            }
        ],

        '5203' => [
            'description' => 'Model onchange: a UI descriptor without value is preserved and not validated.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'selection_only']
                ]);
            },
            'assert'      => function($result) {
                return ($result['string_short'] ?? null) === [
                    'selection' => ['first', 'second']
                ];
            }
        ],

        '5204' => [
            'description' => 'Model onchange: a scalar descriptor value is adapted for JSON output.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'descriptor_datetime']
                ]);
            },
            'assert'      => function($result) {
                return ($result['datetime']['value'] ?? null) === '2024-04-01T16:55:42+00:00'
                    && ($result['datetime']['readonly'] ?? null) === true;
            }
        ],

        '5205' => [
            'description' => 'Model onchange: an optional empty string remains empty and bypasses ORM constraints.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'empty_optional_string']
                ]);
            },
            'assert'      => function($result) {
                return array_key_exists('string_short', $result)
                    && $result['string_short'] === '';
            }
        ],

        '5206' => [
            'description' => 'Model onchange: current object id prevents a unique value from conflicting with itself.',
            'arrange'     => function() {
                return User::id(1)->read(['login'])->first(true);
            },
            'act'         => function($fixtures) {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeUserProbe::class,
                    'id'      => 1,
                    'changes' => ['login' => $fixtures['login']]
                ]);
            },
            'assert'      => function($result) {
                return isset($result['login']) && !is_array($result['login']);
            }
        ],

        '5207' => [
            'description' => 'Model onchange: a unique value conflicting on creation is returned with errors.',
            'arrange'     => function() {
                return User::id(1)->read(['login'])->first(true);
            },
            'act'         => function($fixtures) {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeUserProbe::class,
                    'changes' => ['login' => $fixtures['login']]
                ]);
            },
            'assert'      => function($result) {
                $errors = (array) ($result['login']['errors'] ?? []);

                return isset($errors['duplicate_index']);
            }
        ],

        '5208' => [
            'description' => 'Model onchange: values id is used when the explicit id parameter is absent.',
            'arrange'     => function() {
                return User::id(1)->read(['login'])->first(true);
            },
            'act'         => function($fixtures) {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeUserProbe::class,
                    'changes' => ['login' => $fixtures['login']],
                    'values'  => ['id' => 1]
                ]);
            },
            'assert'      => function($result) {
                return isset($result['login']) && !is_array($result['login']);
            }
        ],

        '5209' => [
            'description' => 'Model onchange: a null descriptor value is preserved with its metadata.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'descriptor_null']
                ]);
            },
            'assert'      => function($result) {
                $field = $result['string_short'] ?? [];

                return array_key_exists('value', $field)
                    && $field['value'] === null
                    && ($field['visible'] ?? null) === true
                    && ($field['required'] ?? null) === false;
            }
        ],

        '5210' => [
            'description' => 'Model onchange: zero and false remain scalar values.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'zero_and_false']
                ]);
            },
            'assert'      => function($result) {
                return array_key_exists('integer_value', $result)
                    && $result['integer_value'] === 0
                    && array_key_exists('boolean_value', $result)
                    && $result['boolean_value'] === false;
            }
        ],

        '5211' => [
            'description' => 'Model onchange: a direct relational array is not treated as a UI descriptor.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'direct_array']
                ]);
            },
            'assert'      => function($result) {
                return ($result['tests1_m2m_ids'] ?? null) === [1, 2];
            }
        ],

        '5212' => [
            'description' => 'Model onchange: an array descriptor value keeps all supported UI metadata.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'descriptor_array']
                ]);
            },
            'assert'      => function($result) {
                return ($result['tests1_m2m_ids'] ?? null) === [
                    'value'     => [1, 2],
                    'domain'    => [['id', '>', 0]],
                    'selection' => [1, 2, 3],
                    'visible'   => true,
                    'readonly'  => false,
                    'required'  => true
                ];
            }
        ],

        '5213' => [
            'description' => 'Model onchange: a many2one sub-object is validated through its identifier.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => OnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'many2one_array']
                ]);
            },
            'assert'      => function($result) {
                return ($result['test1_id'] ?? null) === [
                    'id'   => 7,
                    'name' => 'Related object'
                ];
            }
        ],

        '5214' => [
            'description' => 'Model onchange: inherited output is validated against the requested child entity.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => ChildOnchangeProbe::class,
                    'changes' => ['onchange_mode' => 'inherited_invalid']
                ]);
            },
            'assert'      => function($result) {
                $errors = (array) ($result['string_short']['errors'] ?? []);

                return ($result['string_short']['value'] ?? null) === 'inherited'
                    && isset($errors['child_constraint']);
            }
        ],

        '5215' => [
            'description' => 'Model onchange: constraints receive all values returned by onchange together.',
            'act'         => function() {
                return eQual::run('do', 'core_model_onchange', [
                    'entity'  => ContextConstraintProbe::class,
                    'changes' => ['onchange_mode' => 'context_invalid']
                ]);
            },
            'assert'      => function($result) {
                $errors = (array) ($result['string_short']['errors'] ?? []);

                return isset($errors['currency_mismatch'])
                    && ($result['string_currency'] ?? null) === 'USD';
            }
        ]
    ];
}
