<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

namespace core\tests\fixtures\get_table\default_storage {

    use equal\orm\Model;

    class A extends Model {
    }

    class B extends A {
    }

    class C extends B {
    }
}

namespace {

    use core\tests\fixtures\get_table\default_storage\A as DefaultA;
    use core\tests\fixtures\get_table\default_storage\B as DefaultB;
    use core\tests\fixtures\get_table\default_storage\C as DefaultC;

    $get_table = static function(string $class): string {
        $model = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        return $model->getTable();
    };

    $tests = [

        '1001' => [
            'description'   => "A model inheriting directly from Model uses its own table.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_default_storage_a',
            'test'          => function() use($get_table) {
                return $get_table(DefaultA::class);
            }
        ],

        '1002' => [
            'description'   => "A child uses the table of the class inheriting directly from Model.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_default_storage_a',
            'test'          => function() use($get_table) {
                return $get_table(DefaultB::class);
            }
        ],

        '1003' => [
            'description'   => "A deep descendant uses the root storage table.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_default_storage_a',
            'test'          => function() use($get_table) {
                return $get_table(DefaultC::class);
            }
        ]

    ];
}
