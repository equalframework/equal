<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

$tests = [

    '1001' => [
            'description'   =>  "Checking dependents chaining with computed field of related object.",
            'act'           =>  function () {
                    $result = [];
                    $test = core\test\Test::create(['string_short' => 'test 0'])->read(['id'])->first();
                    $test1 = core\test\Test1::create(['test_id' => $test['id']])->read(['id', 'test'])->first();
                    $result[] = $test1['test'];
                    core\test\Test::id($test['id'])->update(['string_short' => 'test 1']);
                    $test1 = core\test\Test1::id($test1['id'])->read(['test'])->first();
                    $result[] = $test1['test'];
                    return $result;
                },
            'assert'        =>  function($result) {
                    return ($result[0] == 'test 0' && $result[1] == 'test 1');
                }
        ],

    '1002' => [
            'description'   =>  "Checking related dependents reset from a non-default language.",
            'arrange'       =>  function () {
                    $test = core\test\Test::create(['string_short' => 'lang 0'])->read(['id'])->first();
                    $test1 = core\test\Test1::create(['test_id' => $test['id']])->read(['id', 'test'])->first();
                    return [
                        'test_id'  => $test['id'],
                        'test1_id' => $test1['id'],
                        'before'   => $test1['test']
                    ];
                },
            'act'           =>  function ($fixtures) {
                    $lang = constant('DEFAULT_LANG') === 'fr' ? 'en' : 'fr';
                    core\test\Test::id($fixtures['test_id'])->update(['string_short' => 'lang 1'], $lang);
                    $test1 = core\test\Test1::id($fixtures['test1_id'])->read(['test'])->first();
                    return $fixtures + ['after' => $test1['test']];
                },
            'assert'        =>  function($result) {
                    return ($result['before'] == 'lang 0' && $result['after'] == 'lang 1');
                },
            'rollback'      =>  function($result) {
                    core\test\Test1::id($result['test1_id'])->delete(true);
                    core\test\Test::id($result['test_id'])->delete(true);
                }
        ],

    '1003' => [
            'description'   =>  "Checking that stored multilang computed fields are localized before fallback.",
            'arrange'       =>  function () {
                    $om = equal\orm\ObjectManager::getInstance();
                    $lang = constant('DEFAULT_LANG') === 'fr' ? 'en' : 'fr';
                    $message_model_id = $om->create('core\alert\MessageModel', [
                        'name'          => 'test_multilang_computed_'.uniqid(),
                        'label'         => 'Default computed label',
                        'description'   => 'Default computed description'
                    ]);
                    $om->update(
                        'core\alert\MessageModel',
                        $message_model_id,
                        ['label' => 'Localized computed label'],
                        $lang
                    );
                    $message_id = $om->create('core\alert\Message', [
                        'object_class'      => 'core\Group',
                        'object_id'         => 1,
                        'message_model_id'  => $message_model_id
                    ]);
                    $objects = $om->read(
                        'core\alert\Message',
                        [$message_id],
                        ['label'],
                        constant('DEFAULT_LANG')
                    );
                    return [
                        'message_model_id'  => $message_model_id,
                        'message_id'        => $message_id,
                        'lang'              => $lang,
                        'default_value'     => $objects[$message_id]['label']
                    ];
                },
            'act'           =>  function ($fixtures) {
                    $om = equal\orm\ObjectManager::getInstance();
                    $objects = $om->read(
                        'core\alert\Message',
                        [$fixtures['message_id']],
                        ['label'],
                        $fixtures['lang']
                    );
                    $translation_ids = $om->search('core\Translation', [
                        ['language', '=', $fixtures['lang']],
                        ['object_class', '=', 'core\alert\Message'],
                        ['object_field', '=', 'label'],
                        ['object_id', '=', $fixtures['message_id']]
                    ]);
                    return $fixtures + [
                        'localized_value'   => $objects[$fixtures['message_id']]['label'],
                        'translation_ids'   => $translation_ids
                    ];
                },
            'assert'        =>  function ($result) {
                    return $result['default_value'] === 'Default computed label'
                        && $result['localized_value'] === 'Localized computed label'
                        && count($result['translation_ids']) === 1;
                },
            'rollback'      =>  function ($result) {
                    $om = equal\orm\ObjectManager::getInstance();
                    $om->remove('core\alert\Message', [$result['message_id']]);
                    $om->remove('core\alert\MessageModel', [$result['message_model_id']]);
                }
        ]

];
