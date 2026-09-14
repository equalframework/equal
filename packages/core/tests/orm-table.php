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

namespace core\tests\fixtures\get_table\abstract_storage {

    use equal\orm\Model;

    abstract class AbstractRoot extends Model {
    }

    class ConcreteRoot extends AbstractRoot {
    }

    class ConcreteChild extends ConcreteRoot {
    }
}

namespace {

    use core\tests\fixtures\get_table\default_storage\A as DefaultA;
    use core\tests\fixtures\get_table\default_storage\B as DefaultB;
    use core\tests\fixtures\get_table\default_storage\C as DefaultC;
    use core\tests\fixtures\get_table\abstract_storage\AbstractRoot;
    use core\tests\fixtures\get_table\abstract_storage\ConcreteRoot;
    use core\tests\fixtures\get_table\abstract_storage\ConcreteChild;
    use equal\orm\ObjectManager;

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
            'description'   => "Model::isAbstract() distinguishes an abstract parent from its concrete descendants.",
            'return'        => ['array'],
            'expected'      => [true, false, false],
            'test'          => function() {
                return [
                    AbstractRoot::isAbstract(),
                    ConcreteRoot::isAbstract(),
                    ConcreteChild::isAbstract()
                ];
            }
        ],

        '1102' => [
            'description'   => "The ORM rejects abstract models and instantiates their concrete descendants.",
            'return'        => ['array'],
            'expected'      => [true, true, true],
            'test'          => function() {
                $orm = ObjectManager::getInstance();
                return [
                    $orm->getModel(AbstractRoot::class) === false,
                    $orm->getModel(ConcreteRoot::class) instanceof ConcreteRoot,
                    $orm->getModel(ConcreteChild::class) instanceof ConcreteChild
                ];
            }
        ],

        '1103' => [
            'description'   => "The first concrete model below an abstract parent defines the storage table.",
            'return'        => ['array'],
            'expected'      => [
                'core_tests_fixtures_get_table_abstract_storage_concreteroot',
                'core_tests_fixtures_get_table_abstract_storage_concreteroot'
            ],
            'test'          => function() use($get_table) {
                return [
                    $get_table(ConcreteRoot::class),
                    $get_table(ConcreteChild::class)
                ];
            }
        ]

    ];
}
