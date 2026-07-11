<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\test {

    if(!class_exists('core\test\LifecycleProbe', false)) {
        class LifecycleProbe extends Test {

            private static $lifecycle_events = [];

            public static function resetLifecycleEvents() {
                self::$lifecycle_events = [];
            }

            public static function getLifecycleEvents() {
                return self::$lifecycle_events;
            }

            private static function recordLifecycleEvent($hook, $self, $ids, $values) {
                self::$lifecycle_events[] = [
                    'hook'     => $hook,
                    'ids'      => array_values($ids),
                    'self_ids' => array_values($self->ids()),
                    'values'   => array_intersect_key($values, array_flip(['state', 'string_short']))
                ];
            }

            public static function oncreate($self, $ids, $values) {
                self::recordLifecycleEvent('oncreate', $self, $ids, $values);
            }

            public static function onafterinstantiate($self, $ids, $values) {
                self::recordLifecycleEvent('onafterinstantiate', $self, $ids, $values);
            }

            public static function onbeforeinstantiate($self, $ids, $values) {
                self::recordLifecycleEvent('onbeforeinstantiate', $self, $ids, $values);
            }

            public static function onbeforeupdate($self, $ids, $values) {
                self::recordLifecycleEvent('onbeforeupdate', $self, $ids, $values);
            }

            public static function onafterupdate($self, $ids, $values) {
                self::recordLifecycleEvent('onafterupdate', $self, $ids, $values);
            }
        }
    }

    if(!class_exists('core\test\LifecycleConsistencyProbe', false)) {
        class LifecycleConsistencyProbe extends Test {

            public static function getColumns() {
                return [
                    'string_short' => [
                        'type'       => 'string',
                        'usage'      => 'text/plain:9',
                        'required'   => true,
                        'unique'     => true,
                        'dependents' => ['tests1_ids' => ['test']]
                    ]
                ];
            }
        }
    }
}

namespace {

    use core\test\LifecycleConsistencyProbe;
    use core\test\LifecycleProbe;
    use core\test\Test;
    use equal\orm\ObjectManager;

    $tests = [

        '5001' => [
                'description' => 'Lifecycle: Test::create stores instance state by default.',
                'act'         => function () {
                        $test = Test::create(['string_short' => 'create'])
                            ->read(['id'])
                            ->first();

                        return $test['id'] ?? null;
                    },
                'assert'      => function($result) {
                        $test = Test::id($result)
                            ->read(['state'])
                            ->first();

                        return $result > 0
                            && $test
                            && $test['state'] === 'instance';
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            Test::id($result)->delete(true);
                        }
                    }
            ],

        '5002' => [
                'description' => 'Lifecycle: Test::create can keep draft state.',
                'act'         => function () {
                        $test = Test::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'] ?? null;
                    },
                'assert'      => function($result) {
                        $test = Test::id($result)
                            ->read(['state'])
                            ->first();

                        return $result > 0
                            && $test
                            && $test['state'] === 'draft';
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            Test::id($result)->delete(true);
                        }
                    }
            ],

        '5003' => [
                'description' => 'Lifecycle: Test::id()->update instantiates a draft.',
                'arrange'     => function () {
                        $test = Test::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        Test::id($id)
                            ->update(['string_short' => 'inst']);

                        return $id;
                    },
                'assert'      => function($result) {
                        $test = Test::id($result)
                            ->read(['state'])
                            ->first();

                        return $result > 0
                            && $test
                            && $test['state'] === 'instance';
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            Test::id($result)->delete(true);
                        }
                    }
            ],

        '5004' => [
                'description' => 'Lifecycle: Test::id()->update keeps instance state.',
                'arrange'     => function () {
                        $test = Test::create(['string_short' => 'before'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        Test::id($id)
                            ->update(['string_short' => 'after']);

                        return $id;
                    },
                'assert'      => function($result) {
                        $test = Test::id($result)
                            ->read(['state'])
                            ->first();

                        return $result > 0
                            && $test
                            && $test['state'] === 'instance';
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            Test::id($result)->delete(true);
                        }
                    }
            ],

        '5005' => [
                'description' => 'Lifecycle: create invokes the after-create handler.',
                'act'         => function () {
                        LifecycleProbe::resetLifecycleEvents();

                        $test = LifecycleProbe::create(['string_short' => 'create'])
                            ->read(['id'])
                            ->first();

                        return $test['id'] ?? null;
                    },
                'assert'      => function($result) {
                        $events = LifecycleProbe::getLifecycleEvents();
                        $hooks = array_map(
                            function($event) {
                                return $event['hook'];
                            },
                            $events
                        );

                        $index = array_search('oncreate', $hooks, true);
                        $event = ($index !== false) ? $events[$index] : [];

                        return $result > 0
                            && $index !== false
                            && ($event['ids'] ?? null) === [$result]
                            && ($event['self_ids'] ?? null) === [$result]
                            && ($event['values']['state'] ?? null) === 'instance'
                            && ($event['values']['string_short'] ?? null) === 'create';
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            LifecycleProbe::id($result)->delete(true);
                        }
                    }
            ],

        '5006' => [
                'description' => 'Lifecycle: draft update invokes instantiate hooks only.',
                'arrange'     => function () {
                        $test = LifecycleProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        LifecycleProbe::resetLifecycleEvents();

                        LifecycleProbe::id($id)
                            ->update(['string_short' => 'inst']);

                        return $id;
                    },
                'assert'      => function($result) {
                        $events = LifecycleProbe::getLifecycleEvents();
                        $hooks = array_map(
                            function($event) {
                                return $event['hook'];
                            },
                            $events
                        );

                        $test = LifecycleProbe::id($result)
                            ->read(['state'])
                            ->first();

                        return $result > 0
                            && $test
                            && $test['state'] === 'instance'
                            && $hooks === ['onbeforeinstantiate', 'onafterinstantiate'];
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            LifecycleProbe::id($result)->delete(true);
                        }
                    }
            ],

        '5007' => [
                'description' => 'Lifecycle: instance update invokes update hooks.',
                'arrange'     => function () {
                        $test = LifecycleProbe::create(['string_short' => 'before'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        LifecycleProbe::resetLifecycleEvents();

                        LifecycleProbe::id($id)
                            ->update(['string_short' => 'after']);

                        return $id;
                    },
                'assert'      => function($result) {
                        $events = LifecycleProbe::getLifecycleEvents();

                        return $result > 0
                            && count($events) === 2
                            && ($events[0]['hook'] ?? null) === 'onbeforeupdate'
                            && ($events[0]['ids'] ?? null) === [$result]
                            && ($events[0]['self_ids'] ?? null) === [$result]
                            && ($events[0]['values']['state'] ?? null) === 'instance'
                            && ($events[0]['values']['string_short'] ?? null) === 'after'
                            && ($events[1]['hook'] ?? null) === 'onafterupdate'
                            && ($events[1]['ids'] ?? null) === [$result]
                            && ($events[1]['self_ids'] ?? null) === [$result]
                            && ($events[1]['values']['state'] ?? null) === 'instance'
                            && ($events[1]['values']['string_short'] ?? null) === 'after';
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            LifecycleProbe::id($result)->delete(true);
                        }
                    }
            ],

        '5008' => [
                'description' => 'Lifecycle consistency: draft creation allows missing required fields.',
                'act'         => function () {
                        $test = LifecycleConsistencyProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'] ?? null;
                    },
                'assert'      => function($result) {
                        $test = LifecycleConsistencyProbe::id($result)
                            ->read(['state'])
                            ->first();

                        return $result > 0
                            && $test
                            && $test['state'] === 'draft';
                    },
                'rollback'    => function($result) {
                        if($result > 0) {
                            LifecycleConsistencyProbe::id($result)->delete(true);
                        }
                    }
            ],

        '5009' => [
                'description' => 'Lifecycle consistency: instantiating a draft requires mandatory fields.',
                'arrange'     => function () {
                        $test = LifecycleConsistencyProbe::create(['state' => 'draft'])
                            ->read(['id'])
                            ->first();

                        return $test['id'];
                    },
                'act'         => function ($id) {
                        $result = [
                            'id'    => $id,
                            'error' => 0
                        ];

                        try {
                            LifecycleConsistencyProbe::id($id)
                                ->update(['state' => 'instance']);
                        }
                        catch(Exception $e) {
                            $result['error'] = $e->getCode();
                        }

                        $test = LifecycleConsistencyProbe::id($id)
                            ->read(['state'])
                            ->first();

                        $result['state'] = $test['state'] ?? null;

                        return $result;
                    },
                'assert'      => function($result) {
                        return ($result['id'] ?? 0) > 0
                            && ($result['error'] ?? 0) === EQ_ERROR_INVALID_PARAM
                            && ($result['state'] ?? null) === 'draft';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],

        '5010' => [
                'description' => 'Lifecycle compatibility: ObjectManager::update keeps legacy direct unique behavior.',
                'arrange'     => function () {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleConsistencyProbe::getType();
                        $value = 'u'.substr(md5((string) microtime(true)), 0, 8);

                        $draft_id = $om->create($class, [
                                'state'        => 'draft',
                                'string_short' => $value
                            ], null, false);

                        $instance_id = $om->create($class, [
                                'string_short' => $value
                            ], null, false);

                        return [
                            'value'       => $value,
                            'draft_id'    => $draft_id,
                            'instance_id' => $instance_id
                        ];
                    },
                'act'         => function ($fixtures) {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleConsistencyProbe::getType();
                        $result = $fixtures;
                        $result['update'] = $om->update($class, [$fixtures['draft_id']], ['state' => 'instance']);

                        $draft = LifecycleConsistencyProbe::id($fixtures['draft_id'])
                            ->read(['state'])
                            ->first();

                        $result['draft_state'] = $draft['state'] ?? null;

                        return $result;
                    },
                'assert'      => function($result) {
                        return ($result['draft_id'] ?? 0) > 0
                            && ($result['instance_id'] ?? 0) > 0
                            && ($result['update'] ?? 0) === [$result['draft_id']]
                            && ($result['draft_state'] ?? null) === 'instance';
                    },
                'rollback'    => function($result) {
                        $ids = [];
                        if(($result['draft_id'] ?? 0) > 0) {
                            $ids[] = $result['draft_id'];
                        }
                        if(($result['instance_id'] ?? 0) > 0) {
                            $ids[] = $result['instance_id'];
                        }
                        if(count($ids)) {
                            LifecycleConsistencyProbe::ids($ids)->delete(true);
                        }
                    }
            ],
        '5011' => [
                'description' => 'Lifecycle compatibility: ObjectManager::create keeps legacy required failure after insertion.',
                'act'         => function () {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleConsistencyProbe::getType();
                        $domain = [
                            ['state', 'in', ['draft', 'instance']],
                            ['deleted', 'in', ['0', '1']]
                        ];

                        $before = $om->search($class, $domain);
                        $result = $om->create($class, []);
                        $after = $om->search($class, $domain);
                        $created_ids = array_values(array_diff($after, $before));

                        return [
                            'result'       => $result,
                            'before_count' => count($before),
                            'after_count'  => count($after),
                            'created_ids'  => $created_ids
                        ];
                    },
                'assert'      => function($result) {
                        return ($result['result'] ?? 0) === EQ_ERROR_INVALID_PARAM
                            && ($result['after_count'] ?? -1) === (($result['before_count'] ?? -2) + 1)
                            && count($result['created_ids'] ?? []) === 1;
                    },
                'rollback'    => function($result) {
                        $ids = $result['created_ids'] ?? [];
                        if(count($ids)) {
                            LifecycleConsistencyProbe::ids($ids)->delete(true);
                        }
                    }
            ],

        '5012' => [
                'description' => 'Lifecycle consistency: ObjectManager::update enforces required fields on instantiation.',
                'arrange'     => function () {
                        $om = ObjectManager::getInstance();
                        return $om->create(LifecycleConsistencyProbe::getType(), ['state' => 'draft']);
                    },
                'act'         => function ($id) {
                        $om = ObjectManager::getInstance();
                        $class = LifecycleConsistencyProbe::getType();

                        $result = [
                            'id'     => $id,
                            'update' => $om->update($class, [$id], ['state' => 'instance'])
                        ];

                        $object = $om->read($class, [$id], ['state']);
                        $result['state'] = $object[$id]['state'] ?? null;

                        return $result;
                    },
                'assert'      => function($result) {
                        return ($result['id'] ?? 0) > 0
                            && ($result['update'] ?? 0) === EQ_ERROR_INVALID_PARAM
                            && ($result['state'] ?? null) === 'draft';
                    },
                'rollback'    => function($result) {
                        $id = $result['id'] ?? 0;
                        if($id > 0) {
                            LifecycleConsistencyProbe::id($id)->delete(true);
                        }
                    }
            ],
    ];
}
