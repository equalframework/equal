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
}

namespace {

    use core\test\LifecycleProbe;
    use core\test\Test;

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

    ];
}
