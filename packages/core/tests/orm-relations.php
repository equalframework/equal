<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\orm\ObjectManager;
use test\Test as TestModel;
use test\Test1 as Test1Model;
use test\TestOne2manyForeignKey;

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

                                                $test_a_id = $om->create(TestModel::getType(), ['string_short' => 'o2m2520a']);
                                                $test_b_id = $om->create(TestModel::getType(), ['string_short' => 'o2m2520b']);
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
                                                $om->remove(TestModel::getType(), $result['fixtures']['test_ids']);
                                            }
                    )
];
