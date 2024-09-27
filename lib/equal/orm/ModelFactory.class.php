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

    private $root_fields = ['id', 'creator', 'created', 'modifier', 'modified', 'deleted', 'state'];

    private $relationship_field_types = ['many2one', 'one2many', 'many2many'];

    protected function __construct(Container $container) {
    }

    /**
     * Returns one or multiple generated object(s) using the given class' model schema
     *
     * @param class-string $class
     * @param array $options Factory options like qty, values and sequences
     * @return array
     * @throws Exception
     */
    public function create(string $class, array $options = []): array {
        /** @var ObjectManager $orm */
        $orm = $this->container->get('orm');

        $model = $orm->getModel($class);
        if(!$model) {
            throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
        }

        $entities = [];

        $sequence_index = 0;
        $values = !empty($options['sequences']) ? $options['sequences'][0] : ($options['values'] ?? []);

        if(!isset($options['qty'])) {
            $qty = 1;
        }
        elseif(is_array($options['qty'])) {
            $qty = mt_rand($options['qty'][0], $options['qty'][1]);
        }
        else {
            $qty = $options['qty'];
        }

        $schema = $model->getSchema();
        for($i = 1; $i <= $qty; $i++) {
            $entities[] = $this->createEntityFromModelSchema($schema, $values, $options['relationships'] ?? []);

            if(!empty($options['sequences'])) {
                $sequence_index = isset($options['sequences'][++$sequence_index]) ? $sequence_index : 0;

                $values = $options['sequences'][$sequence_index];
            }
        }

        return $entities;
    }

    /**
     * @throws Exception
     */
    private function createEntityFromModelSchema(array $model_schema, array $forced_values = [], array $relationships = []): array {
        $object = [];
        foreach($model_schema as $field => $field_descriptor) {
            $field_type = $field_descriptor['result_type'] ?? $field_descriptor['type'];
            if(array_key_exists($field, $forced_values)) {
                $object[$field] = $forced_values[$field];
                continue;
            }
            elseif(
                in_array($field_type, $this->relationship_field_types)
                && (in_array($field, $relationships) || array_key_exists($field, $relationships))
            ) {
                if($field_type === 'many2one') {
                    $factory_options = $relationships[$field] ?? [];
                    if(isset($factory_options['qty']) && $factory_options['qty'] !== 1) {
                        $factory_options['qty'] = 1;
                    }

                    $object[$field] = $this->create($field_descriptor['foreign_object'], $factory_options)[0];
                } else {
                    $object[$field] = $this->create($field_descriptor['foreign_object'], $relationships[$field] ?? []);
                }
            }

            if(
                isset($object[$field])
                || in_array($field, $this->root_fields)
                || in_array($field_type, $this->relationship_field_types)
            ) {
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
}
