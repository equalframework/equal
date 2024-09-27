<?php

namespace equal\orm;

use equal\data\DataGenerator;
use equal\organic\Service;
use equal\services\Container;
use Exception;

/**
 * @method static ModelFactory getInstance()
 */
class ModelFactory extends Service {

    private $qty = 1;

    private $values = [];

    private $root_fields = ['id', 'creator', 'created', 'modifier', 'modified', 'deleted', 'state'];

    protected function __construct(Container $container) {
    }

    /**
     * Sets the quantity of models to create for the next `ModelFactory::create()` function call
     *
     * @link ModelFactory::create()
     * @throws Exception
     */
    public function qty(int $qty): ModelFactory {
        if($qty < 1) {
            throw new Exception('quantity_must_be_greater_than_zero', EQ_ERROR_INVALID_PARAM);
        }

        $this->qty = $qty;

        return $this;
    }

    /**
     * Sets the forced values of models to create for the next `ModelFactory::create()` function call
     *
     * @link ModelFactory::create()
     * @throws Exception
     */
    public function values(array $values): ModelFactory {
        foreach($values as $field => $value) {
            if(!is_string($field)) {
                throw new Exception('field_must_be_a_string', EQ_ERROR_INVALID_PARAM);
            }
            if(is_array($value) || is_object($value)) {
                throw new Exception('not_expected_value', EQ_ERROR_INVALID_PARAM);
            }
        }

        $this->values = $values;

        return $this;
    }

    /**
     * Returns one or multiple generated object(s) using the given class' model schema
     *
     * @param class-string $class
     * @see ModelFactory::qty() To set the quantity of objects to create (default: `1`)
     * @see ModelFactory::values() To set the forced values of objects to create (default: `[]`)
     * @throws Exception
     */
    public function create(string $class): array {
        /** @var ObjectManager $orm */
        $orm = $this->container->get('orm');

        $model = $orm->getModel($class);
        if(!$model) {
            throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
        }

        $entities = [];
        $schema = $model->getSchema();
        for($i = 1; $i <= $this->qty; $i++) {
            $entities[] = $this->createEntityFromModelSchema($schema, $this->values);
        }

        $this->resetProperties();

        return count($entities) === 1 ? $entities[0] : $entities;
    }

    /**
     * @throws Exception
     */
    private function createEntityFromModelSchema(array $model_schema, array $forced_values = []): array {
        $object = [];
        foreach($model_schema as $field => $field_descriptor) {
            if(array_key_exists($field, $forced_values)) {
                $object[$field] = $forced_values[$field];
                continue;
            }
            elseif(in_array($field, $this->root_fields)) {
                continue;
            }

            $is_required = $field_descriptor['required'] ?? false;
            if(!$is_required && DataGenerator::boolean(0.05)) {
                $object[$field] = null;
            }
            else {
                $object[$field] = DataGenerator::generateFromField($field, $field_descriptor);
            }
        }

        return $object;
    }

    private function resetProperties(): void {
        $this->qty = 1;
        $this->values = [];
    }
}
