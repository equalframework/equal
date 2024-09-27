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
     *       - 'qty': int (optional) The quantity of items.
     *            Example: 2
     *       - 'values': array (optional) A list of values to force for all entities created.
     *            Example: ['is_sent' => false]
     *                      -> All entities created will have `is_sent` value to false
     *      - 'sequences': array (optional) A list of values sequences to force for one entity.
     *           Example: [['name' => 'Group 1'], ['name' => 'Group 2']]
     *                      -> First entity create will have name to "Group 1" and second will have "Group 2", etc.
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

        $qty = $this->extractQtyFromOptions($options);
        $sequences = $this->extractSequencesFromOptions($options);
        $relationships = $this->extractRelationshipsFromOptions($options);

        $entities = [];

        $sequence_index = 0;
        $values = !empty($sequences) ? $sequences[0] : [];
        $schema = $model->getSchema();
        for($i = 1; $i <=$qty; $i++) {
            $entities[] = $this->createEntityFromModelSchema($schema, $values, $relationships);

            if(!empty($sequences)) {
                $sequence_index = isset($sequences[++$sequence_index]) ? $sequence_index : 0;

                $values = $sequences[$sequence_index];
            }
        }

        return $entities;
    }

    private function extractQtyFromOptions(array $options): int {
        $qty = 1;

        if(isset($options['qty'])) {
            if(is_array($options['qty']) && count($options['qty']) === 2 && is_int($options['qty'][0]) && is_int($options['qty'][1])) {
                $qty = mt_rand($options['qty'][0], $options['qty'][1]);
            }
            elseif(is_int($options['qty'])) {
                $qty = $options['qty'];
            } else {
                throw new Exception('invalid_qty', EQ_ERROR_INVALID_PARAM);
            }
        }

        return $qty;
    }

    private function extractSequencesFromOptions(array $options): array {
        $sequences = [];

        if(!empty($options['sequences'])) {
            foreach($options['sequences'] as $index => $values) {
                if(!is_int($index)) {
                    throw new Exception('invalid_sequence_index_must_be_integer', EQ_ERROR_INVALID_PARAM);
                }

                foreach($values as $field => $value) {
                    if(!is_string($field)) {
                        throw new Exception('invalid_sequence_field_must_be_a_string', EQ_ERROR_INVALID_PARAM);
                    }
                    if(is_array($value) || is_object($value)) {
                        throw new Exception('invalid_sequence_not_expected_value', EQ_ERROR_INVALID_PARAM);
                    }
                }
            }

            $sequences = $options['sequences'];
        }

        return $sequences;
    }

    private function extractRelationshipsFromOptions(array $options): array {
        $relationships = [];
        if(!empty($options['relationships'])) {
            foreach($options['relationships'] as $field => $factory_options) {
                if(is_int($field) && is_string($factory_options)) {
                    continue;
                }

                if(!is_string($field) || !is_array($factory_options)) {
                    throw new Exception('invalid_relationship', EQ_ERROR_INVALID_PARAM);
                }
            }

            $relationships = $options['relationships'];
        }

        return $relationships;
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
                || $field_descriptor['type'] === 'computed'
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
