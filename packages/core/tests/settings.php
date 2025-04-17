<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\Setting;

$tests = [

    '1001' => [
        'description' => 'Create a setting without existing section',
        'help' => 'Ensure a section is auto-created when it does not exist.',
        'arrange' => function () {
            return uniqid('section_');
        },
        'act' => function ($section) {
            Setting::assert_value('testpkg', $section, 'param_1001', 'val');
            return Setting::get_value('testpkg', $section, 'param_1001');
        },
        'assert' => function ($result) {
            return $result === 'val';
        }
    ],

    '1002' => [
        'description' => 'Create setting with default value',
        'help' => 'Assert creation of setting and initial default value.',
        'arrange' => function () {
            return true;
        },
        'act' => function () {
            Setting::assert_value('testpkg', 'demo', 'param_1002', 'default');
            return Setting::get_value('testpkg', 'demo', 'param_1002');
        },
        'assert' => function ($val) {
            return $val === 'default';
        }
    ],

    '1003' => [
        'description' => 'Get value for a non-existing setting',
        'help' => 'Should return default if setting not found.',
        'arrange' => function () {
            return true;
        },
        'act' => function () {
            return Setting::get_value('fakepkg', 'demo', 'param_1003', 'fallback');
        },
        'assert' => function ($val) {
            return $val === 'fallback';
        }
    ],

    '1004' => [
        'description' => 'Get value for existing setting with default selector',
        'help' => 'Should retrieve the global default setting value.',
        'arrange' => function () {
            Setting::assert_value('testpkg', 'demo', 'param_1004', 'yes');
        },
        'act' => function () {
            return Setting::get_value('testpkg', 'demo', 'param_1004', 'no');
        },
        'assert' => function ($val) {
            return $val === 'yes';
        }
    ],

    '1005' => [
        'description' => 'Get value for setting with user-specific override',
        'help' => 'Return the value assigned to a specific user_id context.',
        'arrange' => function () {
            Setting::assert_value('testpkg', 'demo', 'param_1005', 'user_val', ['user_id' => 999]);
        },
        'act' => function () {
            return Setting::get_value('testpkg', 'demo', 'param_1005', null, ['user_id' => 999]);
        },
        'assert' => function ($val) {
            return $val === 'user_val';
        }
    ],

    '1011' => [
        'description' => 'fetch_and_add returns incremented value',
        'help' => 'Should increase sequence value atomically.',
        'arrange' => function () {
            Setting::assert_sequence('testpkg', 'seq', 'param_1011', 101, ['user_id' => 10]);
        },
        'act' => function () {
            return Setting::fetch_and_add('testpkg', 'seq', 'param_1011', 1, ['user_id' => 10]);
        },
        'assert' => function ($current) {
            return $current === 101 && 102 === Setting::get_sequence('testpkg', 'seq', 'param_1011', null, ['user_id' => 10]);
        }
    ],

    '1014' => [
        'description' => 'format_number uses correct separators and precision',
        'help' => 'Should apply configured number formatting rules.',
        'arrange' => function () {
            Setting::assert_value('core', 'locale', 'numbers.decimal_separator', ',');
            Setting::assert_value('core', 'locale', 'numbers.thousands_separator', '.');
            Setting::assert_value('core', 'locale', 'numbers.decimal_precision', 2);
        },
        'act' => function () {
            return Setting::format_number(1234.567);
        },
        'assert' => function ($formatted) {
            return $formatted === '1.234,57';
        }
    ],

    '1015' => [
        'description' => 'format_number_currency respects symbol position',
        'help' => 'Applies currency symbol correctly before/after value.',
        'arrange' => function () {
            Setting::assert_value('core', 'locale', 'currency.symbol_position', 'before');
            Setting::assert_value('core', 'locale', 'currency.decimal_precision', 2);
            Setting::assert_value('core', 'locale', 'currency', '€');
        },
        'act' => function () {
            return Setting::format_number_currency(42);
        },
        'assert' => function ($res) {
            return strpos($res, '€ ') === 0;
        }
    ]

];
