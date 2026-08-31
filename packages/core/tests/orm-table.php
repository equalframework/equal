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

namespace core\tests\fixtures\get_table\single_boundary {

    use equal\orm\Model;

    class A extends Model {
    }

    class B extends A {
        public static function getFlags(): int {
            return EQ_FLAG_OWN_TABLE;
        }
    }

    class C extends B {
    }
}

namespace core\tests\fixtures\get_table\multiple_boundaries {

    use equal\orm\Model;

    class A extends Model {
    }

    class B extends A {
        public static function getFlags(): int {
            return EQ_FLAG_OWN_TABLE;
        }
    }

    class C extends B {
    }

    class D extends C {
        public static function getFlags(): int {
            return EQ_FLAG_OWN_TABLE;
        }
    }

    class E extends D {
    }
}

namespace {

    use core\tests\fixtures\get_table\default_storage\A as DefaultA;
    use core\tests\fixtures\get_table\default_storage\B as DefaultB;
    use core\tests\fixtures\get_table\default_storage\C as DefaultC;
    use core\tests\fixtures\get_table\multiple_boundaries\D as MultipleD;
    use core\tests\fixtures\get_table\multiple_boundaries\E as MultipleE;
    use core\tests\fixtures\get_table\single_boundary\B as BoundaryB;
    use core\tests\fixtures\get_table\single_boundary\C as BoundaryC;

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
            'description'   => "A child without OWN_TABLE uses the table of the class inheriting directly from Model.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_default_storage_a',
            'test'          => function() use($get_table) {
                return $get_table(DefaultB::class);
            }
        ],

        '1003' => [
            'description'   => "A deep descendant without OWN_TABLE uses the root storage table.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_default_storage_a',
            'test'          => function() use($get_table) {
                return $get_table(DefaultC::class);
            }
        ],

        '2001' => [
            'description'   => "A model declaring OWN_TABLE uses its own table.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_single_boundary_b',
            'test'          => function() use($get_table) {
                return $get_table(BoundaryB::class);
            }
        ],

        '2002' => [
            'description'   => "A descendant uses the table of the nearest OWN_TABLE ancestor.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_single_boundary_b',
            'test'          => function() use($get_table) {
                return $get_table(BoundaryC::class);
            }
        ],

        '3001' => [
            'description'   => "A second OWN_TABLE declaration creates a new storage boundary.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_multiple_boundaries_d',
            'test'          => function() use($get_table) {
                return $get_table(MultipleD::class);
            }
        ],

        '3002' => [
            'description'   => "A descendant uses the table of the second OWN_TABLE boundary.",
            'return'        => ['string'],
            'expected'      => 'core_tests_fixtures_get_table_multiple_boundaries_d',
            'test'          => function() use($get_table) {
                return $get_table(MultipleE::class);
            }
        ],

        '3003' => [
            'description'   => "An inherited OWN_TABLE flag is not treated as locally declared.",
            'return'        => ['array'],
            'expected'      => [
                'has_flag'      => true,
                'has_own_flag'  => false,
                'table'         => 'core_tests_fixtures_get_table_single_boundary_b'
            ],
            'test'          => function() use($get_table) {
                return [
                    'has_flag'      => BoundaryC::hasFlag(EQ_FLAG_OWN_TABLE),
                    'has_own_flag'  => BoundaryC::hasOwnFlag(EQ_FLAG_OWN_TABLE),
                    'table'         => $get_table(BoundaryC::class)
                ];
            }
        ]

    ];
}
