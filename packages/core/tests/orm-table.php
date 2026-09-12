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

namespace core\tests\fixtures\get_table\storage_boundaries {

    use equal\orm\Model;

    class A extends Model {
    }

    class B extends A {
        public function getTable() {
            return static::getSlug(self::class);
        }
    }

    class C extends B {
    }

    class D extends C {
        public function getTable() {
            return static::getSlug(self::class);
        }
    }

    class E extends D {
    }

    class ExplicitTable extends A {
        public function getTable() {
            return 'explicit_table';
        }
    }
}

namespace core\tests\fixtures\get_table\model_scope {

    use equal\orm\Model;

    class A extends Model {
    }

    class B extends A {
    }

    class C extends B {
    }

    class BExtension extends B {
        public static function getModelScope(): ?string {
            return B::getModelScope();
        }
    }

    class SpecializedB extends BExtension {
        public static function getModelScope(): ?string {
            return static::class;
        }
    }

    class AExtension extends A {
        public static function getModelScope(): ?string {
            return A::getModelScope();
        }
    }

    class DedicatedB extends A {
        public function getTable() {
            return static::getSlug(self::class);
        }
    }
}

namespace {

    use core\tests\fixtures\get_table\default_storage\A as DefaultA;
    use core\tests\fixtures\get_table\default_storage\B as DefaultB;
    use core\tests\fixtures\get_table\default_storage\C as DefaultC;
    use core\tests\fixtures\get_table\storage_boundaries\B as BoundaryB;
    use core\tests\fixtures\get_table\storage_boundaries\C as BoundaryC;
    use core\tests\fixtures\get_table\storage_boundaries\D as BoundaryD;
    use core\tests\fixtures\get_table\storage_boundaries\E as BoundaryE;
    use core\tests\fixtures\get_table\storage_boundaries\ExplicitTable;
    use core\tests\fixtures\get_table\model_scope\A as ScopeA;
    use core\tests\fixtures\get_table\model_scope\AExtension;
    use core\tests\fixtures\get_table\model_scope\B as ScopeB;
    use core\tests\fixtures\get_table\model_scope\BExtension;
    use core\tests\fixtures\get_table\model_scope\C as ScopeC;
    use core\tests\fixtures\get_table\model_scope\DedicatedB;
    use core\tests\fixtures\get_table\model_scope\SpecializedB;

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
        ],

        '1101' => [
            'description' => "A storage boundary uses its declaring class table.",
            'return'      => ['string'],
            'expected'    => 'core_tests_fixtures_get_table_storage_boundaries_b',
            'test'        => fn() => $get_table(BoundaryB::class)
        ],

        '1102' => [
            'description' => "A descendant inherits its parent's storage boundary.",
            'return'      => ['string'],
            'expected'    => 'core_tests_fixtures_get_table_storage_boundaries_b',
            'test'        => fn() => $get_table(BoundaryC::class)
        ],

        '1103' => [
            'description' => "A new override creates a new storage boundary.",
            'return'      => ['array'],
            'expected'    => [
                'core_tests_fixtures_get_table_storage_boundaries_d',
                'core_tests_fixtures_get_table_storage_boundaries_d'
            ],
            'test'        => fn() => [$get_table(BoundaryD::class), $get_table(BoundaryE::class)]
        ],

        '1104' => [
            'description' => "A model may use an explicit table name.",
            'return'      => ['string'],
            'expected'    => 'explicit_table',
            'test'        => fn() => $get_table(ExplicitTable::class)
        ],

        '2001' => [
            'description' => "The hierarchy root has no model scope.",
            'return'      => ['NULL'],
            'expected'    => null,
            'test'        => fn() => ScopeA::getModelScope()
        ],

        '2002' => [
            'description' => "Each normal subtype uses its exact class as model scope.",
            'return'      => ['array'],
            'expected'    => [ScopeB::class, ScopeC::class],
            'test'        => fn() => [ScopeB::getModelScope(), ScopeC::getModelScope()]
        ],

        '2003' => [
            'description' => "A functional extension can reuse another model scope.",
            'return'      => ['string'],
            'expected'    => ScopeB::class,
            'test'        => fn() => BExtension::getModelScope()
        ],

        '2004' => [
            'description' => "An extension can preserve unscoped access to a root table.",
            'return'      => ['NULL'],
            'expected'    => null,
            'test'        => fn() => AExtension::getModelScope()
        ],

        '2005' => [
            'description' => "A descendant of an extension can become a distinct persistent subtype.",
            'return'      => ['string'],
            'expected'    => SpecializedB::class,
            'test'        => fn() => SpecializedB::getModelScope()
        ],

        '2006' => [
            'description' => "A dedicated table does not implicitly disable model scoping.",
            'return'      => ['string'],
            'expected'    => DedicatedB::class,
            'test'        => fn() => DedicatedB::getModelScope()
        ],

        '2101' => [
            'description' => "The system model column defaults to the effective persistent model.",
            'return'      => ['array'],
            'expected'    => [ScopeA::class, ScopeB::class, ScopeB::class],
            'test'        => function() {
                return [
                    ScopeA::getSpecialColumns()['model']['default'],
                    ScopeB::getSpecialColumns()['model']['default'],
                    BExtension::getSpecialColumns()['model']['default']
                ];
            }
        ]

    ];
}
