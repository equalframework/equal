<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\tests\fixtures\orm_relations {

    use core\test\Test;
    use core\test\Test1;

    class NullParent extends Test {
        public static function getColumns() {
            return [
                'tests1_ids' => [
                    'type'           => 'one2many',
                    'foreign_object' => NullChild::class,
                    'foreign_field'  => 'test_id',
                    'ondetach'       => 'null'
                ]
            ];
        }
    }

    class NullChild extends Test1 {
        public static function getColumns() {
            return [
                'test_id' => [
                    'type'           => 'many2one',
                    'foreign_object' => NullParent::class,
                    'ondelete'       => 'null'
                ]
            ];
        }
    }

    class CascadeParent extends Test {
        public static function getColumns() {
            return [
                'tests1_ids' => [
                    'type'           => 'one2many',
                    'foreign_object' => CascadeChild::class,
                    'foreign_field'  => 'test_id'
                ]
            ];
        }
    }

    class CascadeChild extends Test1 {
        public static function getColumns() {
            return [
                'test_id' => [
                    'type'           => 'many2one',
                    'foreign_object' => CascadeParent::class,
                    'ondelete'       => 'cascade'
                ]
            ];
        }
    }

    class DetachDeleteParent extends Test {
        public static function getColumns() {
            return [
                'tests1_ids' => [
                    'type'           => 'one2many',
                    'foreign_object' => DetachDeleteChild::class,
                    'foreign_field'  => 'test_id',
                    'ondetach'       => 'delete'
                ]
            ];
        }
    }

    class DetachDeleteChild extends Test1 {
        public static function getColumns() {
            return [
                'test_id' => [
                    'type'           => 'many2one',
                    'foreign_object' => DetachDeleteParent::class,
                    'ondelete'       => 'null'
                ]
            ];
        }
    }

    class HandlerParent extends Test {
        public static function getColumns() {
            return [
                'tests1_ids' => [
                    'type'           => 'one2many',
                    'foreign_object' => HandlerChild::class,
                    'foreign_field'  => 'test_id',
                    'ondetach'       => 'handleDetach'
                ]
            ];
        }

        public static function handleDetach(array $values): void {
            HandlerChild::ids($values)->delete(true);
        }

        public static function handleDelete(array $ids): void {
            HandlerChild::ids($ids)->delete(true);
        }
    }

    class HandlerChild extends Test1 {
        public static function getColumns() {
            return [
                'test_id' => [
                    'type'           => 'many2one',
                    'foreign_object' => HandlerParent::class,
                    'ondelete'       => 'handleDelete'
                ]
            ];
        }
    }
}

namespace {

use equal\orm\ObjectManager;
use core\test\Test as TestModel;
use core\test\Test1 as Test1Model;
use core\test\TestOne2manyForeignKey;
use core\tests\fixtures\orm_relations\CascadeChild;
use core\tests\fixtures\orm_relations\CascadeParent;
use core\tests\fixtures\orm_relations\DetachDeleteChild;
use core\tests\fixtures\orm_relations\DetachDeleteParent;
use core\tests\fixtures\orm_relations\HandlerChild;
use core\tests\fixtures\orm_relations\HandlerParent;
use core\tests\fixtures\orm_relations\NullChild;
use core\tests\fixtures\orm_relations\NullParent;

$tests = [

    '2510' => array(
                    'description'       =>  "Search for some object : clause 'contains' on one2many field.",
                    'return'            =>  array('boolean'),
                    'arrange'           =>  function () {
                                                $om = ObjectManager::getInstance();

                                                $test_a_id = $om->create(TestModel::getType(), ['string_short' => 'o2m2510a']);
                                                $test_b_id = $om->create(TestModel::getType(), ['string_short' => 'o2m2510b']);
                                                $test1_a_id = $om->create(Test1Model::getType(), ['test_id' => $test_a_id]);
                                                $test1_b_id = $om->create(Test1Model::getType(), ['test_id' => $test_b_id]);

                                                return [
                                                    'test_ids'  => [$test_a_id, $test_b_id],
                                                    'test1_ids' => [$test1_a_id, $test1_b_id]
                                                ];
                                            },
                    'act'               =>  function ($fixtures) {
                                                $om = ObjectManager::getInstance();

                                                return [
                                                    'ids'      => $om->search(TestModel::getType(), ['tests1_ids', 'contains', [$fixtures['test1_ids'][0]]]),
                                                    'fixtures' => $fixtures
                                                ];
                                            },
                    'assert'            =>  function ($result) {
                                                if(!is_array($result) || !isset($result['ids']) || !is_array($result['ids'])) {
                                                    return false;
                                                }

                                                $ids = array_map('intval', $result['ids']);

                                                return (
                                                    in_array($result['fixtures']['test_ids'][0], $ids, true)
                                                    && !in_array($result['fixtures']['test_ids'][1], $ids, true)
                                                );
                                            },
                    'rollback'          =>  function ($result) {
                                                if(!isset($result['fixtures'])) {
                                                    return;
                                                }

                                                $om = ObjectManager::getInstance();
                                                $om->remove(Test1Model::getType(), $result['fixtures']['test1_ids']);
                                                $om->remove(TestModel::getType(), $result['fixtures']['test_ids']);
                                            },
                    ),
    '2520' => array(
                    'description'       =>  "Search for some object : clause 'contains' on one2many field (using a foreign key different from 'id').",
                    'return'            =>  array('boolean'),
                    'arrange'           =>  function () {
                                                $om = ObjectManager::getInstance();

                                                $test_a_id = $om->create(TestOne2manyForeignKey::getType(), ['string_short' => 'o2m2520a']);
                                                $test_b_id = $om->create(TestOne2manyForeignKey::getType(), ['string_short' => 'o2m2520b']);
                                                $test1_a_id = $om->create(Test1Model::getType(), ['test_id' => $test_a_id]);
                                                $test1_b_id = $om->create(Test1Model::getType(), ['test_id' => $test_b_id]);

                                                return [
                                                    'test_ids'  => [$test_a_id, $test_b_id],
                                                    'test1_ids' => [$test1_a_id, $test1_b_id]
                                                ];
                                            },
                    'act'               =>  function ($fixtures) {
                                                $om = ObjectManager::getInstance();

                                                return [
                                                    'ids'      => $om->search(TestOne2manyForeignKey::getType(), ['tests1_by_test_id_ids', 'contains', [$fixtures['test_ids'][0]]]),
                                                    'fixtures' => $fixtures
                                                ];
                                            },
                    'assert'            =>  function ($result) {
                                                if(!is_array($result) || !isset($result['ids']) || !is_array($result['ids'])) {
                                                    return false;
                                                }

                                                $ids = array_map('intval', $result['ids']);

                                                return (
                                                    in_array($result['fixtures']['test_ids'][0], $ids, true)
                                                    && !in_array($result['fixtures']['test_ids'][1], $ids, true)
                                                );
                                            },
                    'rollback'          =>  function ($result) {
                                                if(!isset($result['fixtures'])) {
                                                    return;
                                                }

                                                $om = ObjectManager::getInstance();
                                                $om->remove(Test1Model::getType(), $result['fixtures']['test1_ids']);
                                                $om->remove(TestModel::getType(), $result['fixtures']['test_ids']);
                                            }
                    ),
    '2530' => array(
                    'description'       =>  "Search for some object : clause 'contains' on many2one field.",
                    'return'            =>  array('boolean'),
                    'arrange'           =>  function () {
                                                $om = ObjectManager::getInstance();

                                                $test_a_id = $om->create(TestModel::getType(), ['string_short' => 'm2o2530a']);
                                                $test_b_id = $om->create(TestModel::getType(), ['string_short' => 'm2o2530b']);
                                                $test1_a_id = $om->create(Test1Model::getType(), ['test_id' => $test_a_id]);
                                                $test1_b_id = $om->create(Test1Model::getType(), ['test_id' => $test_b_id]);

                                                return [
                                                    'test_ids'  => [$test_a_id, $test_b_id],
                                                    'test1_ids' => [$test1_a_id, $test1_b_id]
                                                ];
                                            },
                    'act'               =>  function ($fixtures) {
                                                $om = ObjectManager::getInstance();

                                                return [
                                                    'ids'      => $om->search(Test1Model::getType(), ['test_id', 'contains', [$fixtures['test_ids'][0]]]),
                                                    'fixtures' => $fixtures
                                                ];
                                            },
                    'assert'            =>  function ($result) {
                                                if(!is_array($result) || !isset($result['ids']) || !is_array($result['ids'])) {
                                                    return false;
                                                }

                                                $ids = array_map('intval', $result['ids']);

                                                return (
                                                    in_array($result['fixtures']['test1_ids'][0], $ids, true)
                                                    && !in_array($result['fixtures']['test1_ids'][1], $ids, true)
                                                );
                                            },
                    'rollback'          =>  function ($result) {
                                                if(!isset($result['fixtures'])) {
                                                    return;
                                                }

                                                $om = ObjectManager::getInstance();
                                                $om->remove(Test1Model::getType(), $result['fixtures']['test1_ids']);
                                                $om->remove(TestOne2manyForeignKey::getType(), $result['fixtures']['test_ids']);
                                            }
                    ),

    '2601' => [
        'description' => 'Relation contract: create and read a many2one/one2many relation from both sides.',
        'arrange'     => function() {
            $parent = NullParent::create(['string_short' => 'p2601'])->read(['id'])->first();
            $child = NullChild::create(['string_short' => 'c2601', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            $parent = NullParent::id($fixtures['parent_id'])->read(['tests1_ids'])->first();
            $child = NullChild::id($fixtures['child_id'])->read(['test_id'])->first();

            return $fixtures + [
                'parent_children' => $parent['tests1_ids'] ?? [],
                'child_parent'    => $child['test_id'] ?? null
            ];
        },
        'assert'      => function($result) {
            return $result['parent_children'] === [$result['child_id']]
                && $result['child_parent'] === $result['parent_id'];
        },
        'rollback'    => function($result) {
            NullChild::id($result['child_id'] ?? 0)->delete(true);
            NullParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2602' => [
        'description' => 'Relation contract: updating a many2one reassigns the inverse one2many relation.',
        'arrange'     => function() {
            $parent_a = NullParent::create(['string_short' => 'pa2602'])->read(['id'])->first();
            $parent_b = NullParent::create(['string_short' => 'pb2602'])->read(['id'])->first();
            $child = NullChild::create(['string_short' => 'c2602', 'test_id' => $parent_a['id']])->read(['id'])->first();

            return [
                'parent_a_id' => $parent_a['id'],
                'parent_b_id' => $parent_b['id'],
                'child_id'    => $child['id']
            ];
        },
        'act'         => function($fixtures) {
            NullChild::id($fixtures['child_id'])->update(['test_id' => $fixtures['parent_b_id']]);

            $parent_a = NullParent::id($fixtures['parent_a_id'])->read(['tests1_ids'])->first();
            $parent_b = NullParent::id($fixtures['parent_b_id'])->read(['tests1_ids'])->first();

            return $fixtures + [
                'parent_a_children' => $parent_a['tests1_ids'] ?? [],
                'parent_b_children' => $parent_b['tests1_ids'] ?? []
            ];
        },
        'assert'      => function($result) {
            return $result['parent_a_children'] === []
                && $result['parent_b_children'] === [$result['child_id']];
        },
        'rollback'    => function($result) {
            NullChild::id($result['child_id'] ?? 0)->delete(true);
            NullParent::ids([$result['parent_a_id'] ?? 0, $result['parent_b_id'] ?? 0])->delete(true);
        }
    ],

    '2603' => [
        'description' => 'Relation contract: ondetach null preserves the child and clears its foreign key.',
        'arrange'     => function() {
            $parent = NullParent::create(['string_short' => 'p2603'])->read(['id'])->first();
            $child = NullChild::create(['string_short' => 'c2603', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            NullParent::id($fixtures['parent_id'])->update(['tests1_ids' => [-$fixtures['child_id']]]);

            return $fixtures + [
                'detached_ids' => NullChild::search([
                    ['id', '=', $fixtures['child_id']],
                    ['test_id', 'is', null]
                ])->ids()
            ];
        },
        'assert'      => function($result) {
            return $result['detached_ids'] === [$result['child_id']];
        },
        'rollback'    => function($result) {
            NullChild::id($result['child_id'] ?? 0)->delete(true);
            NullParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2604' => [
        'description' => 'Relation contract: ondetach delete permanently removes the detached child.',
        'arrange'     => function() {
            $parent = DetachDeleteParent::create(['string_short' => 'p2604'])->read(['id'])->first();
            $child = DetachDeleteChild::create(['string_short' => 'c2604', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            DetachDeleteParent::id($fixtures['parent_id'])->update(['tests1_ids' => [-$fixtures['child_id']]]);

            return $fixtures + [
                'child' => DetachDeleteChild::id($fixtures['child_id'])->read(['id'])->first()
            ];
        },
        'assert'      => fn($result) => $result['child'] === null,
        'rollback'    => function($result) {
            DetachDeleteChild::id($result['child_id'] ?? 0)->delete(true);
            DetachDeleteParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2605' => [
        'description' => 'Relation contract: ondelete null preserves the child and clears its foreign key.',
        'arrange'     => function() {
            $parent = NullParent::create(['string_short' => 'p2605'])->read(['id'])->first();
            $child = NullChild::create(['string_short' => 'c2605', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            NullParent::id($fixtures['parent_id'])->delete();
            $child = NullChild::id($fixtures['child_id'])->read(['id', 'test_id', 'deleted'])->first();

            return $fixtures + ['child' => is_null($child) ? null : $child->toArray()];
        },
        'assert'      => function($result) {
            return ($result['child']['id'] ?? null) === $result['child_id']
                && ($result['child']['test_id'] ?? null) === null
                && ($result['child']['deleted'] ?? true) === false;
        },
        'rollback'    => function($result) {
            NullChild::id($result['child_id'] ?? 0)->delete(true);
            NullParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2606' => [
        'description' => 'Relation contract: ondelete cascade propagates a soft deletion to the child.',
        'arrange'     => function() {
            $parent = CascadeParent::create(['string_short' => 'p2606'])->read(['id'])->first();
            $child = CascadeChild::create(['string_short' => 'c2606', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            CascadeParent::id($fixtures['parent_id'])->delete();
            $child = CascadeChild::id($fixtures['child_id'])->read(['id', 'deleted'])->first();

            return $fixtures + ['child' => is_null($child) ? null : $child->toArray()];
        },
        'assert'      => function($result) {
            return ($result['child']['id'] ?? null) === $result['child_id']
                && ($result['child']['deleted'] ?? false) === true;
        },
        'rollback'    => function($result) {
            CascadeChild::id($result['child_id'] ?? 0)->delete(true);
            CascadeParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2607' => [
        'description' => 'Relation contract: ondelete cascade propagates a permanent deletion to the child.',
        'arrange'     => function() {
            $parent = CascadeParent::create(['string_short' => 'p2607'])->read(['id'])->first();
            $child = CascadeChild::create(['string_short' => 'c2607', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            CascadeParent::id($fixtures['parent_id'])->delete(true);

            return $fixtures + [
                'child' => CascadeChild::id($fixtures['child_id'])->read(['id'])->first()
            ];
        },
        'assert'      => fn($result) => $result['child'] === null,
        'rollback'    => function($result) {
            CascadeChild::id($result['child_id'] ?? 0)->delete(true);
            CascadeParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2608' => [
        'description' => 'Relation contract: a custom ondetach handler owns the resulting relation state.',
        'arrange'     => function() {
            $parent = HandlerParent::create(['string_short' => 'p2608'])->read(['id'])->first();
            $child = HandlerChild::create(['string_short' => 'c2608', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            HandlerParent::id($fixtures['parent_id'])->update(['tests1_ids' => [-$fixtures['child_id']]]);

            return $fixtures + [
                'child' => HandlerChild::id($fixtures['child_id'])->read(['id'])->first()
            ];
        },
        'assert'      => fn($result) => $result['child'] === null,
        'rollback'    => function($result) {
            HandlerChild::id($result['child_id'] ?? 0)->delete(true);
            HandlerParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2609' => [
        'description' => 'Relation contract: a custom ondelete handler owns the resulting child state.',
        'arrange'     => function() {
            $parent = HandlerParent::create(['string_short' => 'p2609'])->read(['id'])->first();
            $child = HandlerChild::create(['string_short' => 'c2609', 'test_id' => $parent['id']])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            HandlerParent::id($fixtures['parent_id'])->delete();

            return $fixtures + [
                'child' => HandlerChild::id($fixtures['child_id'])->read(['id'])->first()
            ];
        },
        'assert'      => fn($result) => $result['child'] === null,
        'rollback'    => function($result) {
            HandlerChild::id($result['child_id'] ?? 0)->delete(true);
            HandlerParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ],

    '2610' => [
        'description' => 'Relation contract: create persists a many2many relation readable from both sides.',
        'arrange'     => function() {
            $child = Test1Model::create()->read(['id'])->first();
            $parent = TestModel::create([
                'string_short'   => 'p2610',
                'tests1_m2m_ids' => [$child['id']]
            ])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            $parent = TestModel::id($fixtures['parent_id'])->read(['tests1_m2m_ids'])->first();
            $child = Test1Model::id($fixtures['child_id'])->read(['tests_m2m_ids'])->first();

            return $fixtures + [
                'parent_children' => $parent['tests1_m2m_ids'] ?? [],
                'child_parents'   => $child['tests_m2m_ids'] ?? []
            ];
        },
        'assert'      => function($result) {
            return $result['parent_children'] === [$result['child_id']]
                && $result['child_parents'] === [$result['parent_id']];
        },
        'rollback'    => function($result) {
            TestModel::id($result['parent_id'] ?? 0)->delete(true);
            Test1Model::id($result['child_id'] ?? 0)->delete(true);
        }
    ],

    '2611' => [
        'description' => 'Relation contract: update attaches and detaches a many2many relation symmetrically.',
        'arrange'     => function() {
            $parent = TestModel::create(['string_short' => 'p2611'])->read(['id'])->first();
            $child = Test1Model::create()->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            TestModel::id($fixtures['parent_id'])->update(['tests1_m2m_ids' => [$fixtures['child_id']]]);
            $attached = Test1Model::id($fixtures['child_id'])->read(['tests_m2m_ids'])->first();

            TestModel::id($fixtures['parent_id'])->update(['tests1_m2m_ids' => [-$fixtures['child_id']]]);
            $parent = TestModel::id($fixtures['parent_id'])->read(['tests1_m2m_ids'])->first();
            $child = Test1Model::id($fixtures['child_id'])->read(['tests_m2m_ids'])->first();

            return $fixtures + [
                'attached_parents' => $attached['tests_m2m_ids'] ?? [],
                'parent_children'  => $parent['tests1_m2m_ids'] ?? [],
                'child_parents'    => $child['tests_m2m_ids'] ?? []
            ];
        },
        'assert'      => function($result) {
            return $result['attached_parents'] === [$result['parent_id']]
                && $result['parent_children'] === []
                && $result['child_parents'] === [];
        },
        'rollback'    => function($result) {
            TestModel::id($result['parent_id'] ?? 0)->delete(true);
            Test1Model::id($result['child_id'] ?? 0)->delete(true);
        }
    ],

    '2612' => [
        'description' => 'Relation contract: deleting an object removes its many2many links without deleting the target.',
        'arrange'     => function() {
            $parent = TestModel::create(['string_short' => 'p2612'])->read(['id'])->first();
            $child = Test1Model::create()->read(['id'])->first();
            TestModel::id($parent['id'])->update(['tests1_m2m_ids' => [$child['id']]]);

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            TestModel::id($fixtures['parent_id'])->delete();
            $child = Test1Model::id($fixtures['child_id'])->read(['id', 'tests_m2m_ids', 'deleted'])->first();

            return $fixtures + ['child' => is_null($child) ? null : $child->toArray()];
        },
        'assert'      => function($result) {
            return ($result['child']['id'] ?? null) === $result['child_id']
                && ($result['child']['tests_m2m_ids'] ?? null) === []
                && ($result['child']['deleted'] ?? true) === false;
        },
        'rollback'    => function($result) {
            TestModel::id($result['parent_id'] ?? 0)->delete(true);
            Test1Model::id($result['child_id'] ?? 0)->delete(true);
        }
    ],

    '2613' => [
        'description' => 'Relation contract: updating a one2many attaches an existing child from both sides.',
        'arrange'     => function() {
            $parent = NullParent::create(['string_short' => 'p2613'])->read(['id'])->first();
            $child = NullChild::create(['string_short' => 'c2613'])->read(['id'])->first();

            return ['parent_id' => $parent['id'], 'child_id' => $child['id']];
        },
        'act'         => function($fixtures) {
            NullParent::id($fixtures['parent_id'])->update(['tests1_ids' => [$fixtures['child_id']]]);
            $parent = NullParent::id($fixtures['parent_id'])->read(['tests1_ids'])->first();

            return $fixtures + [
                'parent_children' => $parent['tests1_ids'] ?? [],
                'attached_ids'    => NullChild::search([
                    ['id', '=', $fixtures['child_id']],
                    ['test_id', '=', $fixtures['parent_id']]
                ])->ids()
            ];
        },
        'assert'      => function($result) {
            return $result['parent_children'] === [$result['child_id']]
                && $result['attached_ids'] === [$result['child_id']];
        },
        'rollback'    => function($result) {
            NullChild::id($result['child_id'] ?? 0)->delete(true);
            NullParent::id($result['parent_id'] ?? 0)->delete(true);
        }
    ]
];

}
