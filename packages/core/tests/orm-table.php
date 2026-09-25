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

namespace core\tests\fixtures\get_table\explicit_storage {

    use equal\orm\Model;

    class SharedRoot extends Model {
    }

    class StorageRoot extends SharedRoot {

        public static function getModelTable(): string {
            return 'core_tests_fixtures_get_table_explicit_storage_root';
        }
    }

    class StorageChild extends StorageRoot {
    }

}

namespace core\tests\fixtures\get_table\abstract_shared_storage {

    use equal\orm\Model;

    abstract class AbstractRoot extends Model {

        public static function getModelTable(): string {
            return 'core_tests_fixtures_get_table_abstract_shared_storage';
        }
    }

    class ConcreteRoot extends AbstractRoot {
    }

    class ConcreteChild extends ConcreteRoot {
    }
}

namespace core\tests\fixtures\get_table\trait_storage {

    use equal\orm\Model;
    use equal\orm\traits\HasOwnTable;

    class SharedRoot extends Model {
    }

    class StorageRoot extends SharedRoot {

        use HasOwnTable;
    }

    class StorageChild extends StorageRoot {
    }
}

namespace core\tests\fixtures\get_table\trait_scope {

    use equal\orm\Model;
    use equal\orm\traits\IsNotScoped;

    class SharedRoot extends Model {
    }

    class ScopedRoot extends SharedRoot {
    }

    class ScopedChild extends ScopedRoot {
    }

    class UnscopedChild extends SharedRoot {

        use IsNotScoped;
    }

    class UnscopedGrandChild extends UnscopedChild {
    }
}

namespace {

    use core\tests\fixtures\get_table\default_storage\A as DefaultA;
    use core\tests\fixtures\get_table\default_storage\B as DefaultB;
    use core\tests\fixtures\get_table\default_storage\C as DefaultC;
    use core\tests\fixtures\get_table\abstract_storage\AbstractRoot;
    use core\tests\fixtures\get_table\abstract_storage\ConcreteRoot;
    use core\tests\fixtures\get_table\abstract_storage\ConcreteChild;
    use core\tests\fixtures\get_table\explicit_storage\StorageRoot;
    use core\tests\fixtures\get_table\explicit_storage\StorageChild;
    use core\tests\fixtures\get_table\abstract_shared_storage\ConcreteRoot as AbstractSharedConcreteRoot;
    use core\tests\fixtures\get_table\abstract_shared_storage\ConcreteChild as AbstractSharedConcreteChild;
    use core\tests\fixtures\get_table\trait_scope\ScopedChild;
    use core\tests\fixtures\get_table\trait_scope\ScopedRoot;
    use core\tests\fixtures\get_table\trait_scope\UnscopedChild;
    use core\tests\fixtures\get_table\trait_scope\UnscopedGrandChild;
    use core\tests\fixtures\get_table\trait_storage\StorageChild as TraitStorageChild;
    use core\tests\fixtures\get_table\trait_storage\StorageRoot as TraitStorageRoot;
    use core\email\Email;
    use core\security\factor\Passkey;
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
        ],

        '1104' => [
            'description'   => "A concrete storage root below an abstract model has no discriminator, while its child does.",
            'return'        => ['array'],
            'expected'      => [true, ConcreteChild::class],
            'test'          => function() {
                return [
                    is_null(ConcreteRoot::getModelScope()),
                    ConcreteChild::getModelScope()
                ];
            }
        ],

        '1105' => [
            'description'   => "A model defining a distinct table has no discriminator, while descendants sharing that table do.",
            'return'        => ['array'],
            'expected'      => [
                'table'         => 'core_tests_fixtures_get_table_explicit_storage_root',
                'root_unscoped' => true,
                'child_scope'   => StorageChild::class
            ],
            'test'          => function() {
                return [
                    'table'         => StorageRoot::getModelTable(),
                    'root_unscoped' => is_null(StorageRoot::getModelScope()),
                    'child_scope'   => StorageChild::getModelScope()
                ];
            }
        ],

        '1106' => [
            'description'   => "The object root is the first class using the model storage table.",
            'return'        => ['array'],
            'expected'      => [DefaultA::class, ConcreteRoot::class, StorageRoot::class],
            'test'          => function() {
                return [
                    ObjectManager::getObjectRootClass(DefaultC::class),
                    ObjectManager::getObjectRootClass(ConcreteChild::class),
                    ObjectManager::getObjectRootClass(StorageChild::class)
                ];
            }
        ],

        '1107' => [
            'description'   => "The ORM returns the unknown-object error code when an abstract model has no table.",
            'return'        => ['integer'],
            'expected'      => EQ_ERROR_UNKNOWN_OBJECT,
            'test'          => function() {
                return ObjectManager::getInstance()->getObjectTableName(AbstractRoot::class);
            }
        ],

        '1108' => [
            'description'   => "The ORM returns the unknown-object error code when a model table cannot be resolved.",
            'return'        => ['integer'],
            'expected'      => EQ_ERROR_UNKNOWN_OBJECT,
            'test'          => function() {
                return ObjectManager::getInstance()->getObjectTableName('unknown\\MissingModel');
            }
        ],

        '1109' => [
            'description'   => "The object root never crosses an abstract parent sharing the same table.",
            'return'        => ['array'],
            'expected'      => [AbstractSharedConcreteRoot::class, AbstractSharedConcreteRoot::class],
            'test'          => function() {
                return [
                    ObjectManager::getObjectRootClass(AbstractSharedConcreteRoot::class),
                    ObjectManager::getObjectRootClass(AbstractSharedConcreteChild::class)
                ];
            }
        ],

        '1110' => [
            'description'   => "The ORM returns null when a model scope cannot be resolved.",
            'return'        => ['NULL'],
            'expected'      => null,
            'test'          => function() {
                $method = new ReflectionMethod(ObjectManager::class, 'getObjectModelScope');
                $method->setAccessible(true);
                return $method->invoke(ObjectManager::getInstance(), 'unknown\\MissingModel');
            }
        ],

        '1111' => [
            'description'   => "HasOwnTable defines a storage boundary inherited by descendants.",
            'return'        => ['array'],
            'expected'      => [
                TraitStorageRoot::getSlug(TraitStorageRoot::class),
                TraitStorageRoot::getSlug(TraitStorageRoot::class)
            ],
            'test'          => function() {
                return [
                    TraitStorageRoot::getModelTable(),
                    TraitStorageChild::getModelTable()
                ];
            }
        ],

        '1112' => [
            'description'   => "The default model scope uses the discriminator of the called model.",
            'return'        => ['array'],
            'expected'      => [ScopedRoot::class, ScopedChild::class],
            'test'          => function() {
                return [
                    ScopedRoot::getModelScope(),
                    ScopedChild::getModelScope()
                ];
            }
        ],

        '1113' => [
            'description'   => "IsNotScoped disables discriminator filtering for the model and its descendants.",
            'return'        => ['array'],
            'expected'      => [true, true],
            'test'          => function() {
                return [
                    is_null(UnscopedChild::getModelScope()),
                    is_null(UnscopedGrandChild::getModelScope())
                ];
            }
        ],

        '1201' => [
            'description'   => "A virtual root model keeps its parent's custom table and discriminator.",
            'return'        => ['array'],
            'expected'      => [
                'table'                    => 'core_mail',
                'inherits_discriminator'   => true,
                'inherits_root'            => true
            ],
            'test'          => function() {
                $virtual_class = 'virtual\\' . Email::class;
                $virtual_model = ObjectManager::getInstance()->getModel($virtual_class);

                if(!$virtual_model) {
                    return [];
                }

                return [
                    'table'                    => $virtual_model->getTable(),
                    'inherits_discriminator'   => $virtual_class::getModelScope() === Email::getModelScope(),
                    'inherits_root'            => ObjectManager::getObjectRootClass($virtual_class) === ObjectManager::getObjectRootClass(Email::class)
                ];
            }
        ],

        '1202' => [
            'description'   => "A virtual subtype keeps its parent's table and non-null discriminator.",
            'return'        => ['array'],
            'expected'      => [
                'table'         => 'core_security_authenticationfactor',
                'discriminator' => Passkey::class,
                'inherits_root' => true
            ],
            'test'          => function() {
                $virtual_class = 'virtual\\' . Passkey::class;
                $virtual_model = ObjectManager::getInstance()->getModel($virtual_class);

                if(!$virtual_model) {
                    return [];
                }

                return [
                    'table'         => $virtual_model->getTable(),
                    'discriminator' => $virtual_class::getModelScope(),
                    'inherits_root' => ObjectManager::getObjectRootClass($virtual_class) === ObjectManager::getObjectRootClass(Passkey::class)
                ];
            }
        ]

    ];
}
