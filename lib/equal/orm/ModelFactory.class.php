<?php

namespace equal\orm;

use equal\data\DataGenerator;
use Exception;

class ModelFactory {

    private static $root_fields = ['id', 'creator', 'created', 'modifier', 'modified', 'deleted', 'state'];

    private static $relationship_field_types = ['many2one', 'one2many', 'many2many'];

    /**
     * Returns one or multiple generated object(s) using the given class' model schema
     *
     * @param class-string $class
     * @param array $options Factory options like qty, values, sequences, unique and relations
     *       - 'qty': int|array (optional) The quantity of items (default 1).
     *            Example: (int): 2
     *                     (array): [1, 5] (between 1 and 5 included)
     *       - 'values': array (optional) A list of values to force for all entities created.
     *            Example: ['is_sent' => false]
     *                      -> All entities created will have `is_sent` set to false.
     *      - 'sequences': array (optional) A list of values sequences to force for one entity.
     *            Example: [['name' => 'Group 1'], ['name' => 'Group 2']]
     *                      -> First entity created will have `name` set to "Group 1" and second will have `name` set to "Group 2", etc.
     *      - 'unique': bool (optional) Should the entities returned follow unique fields constraints (default true).
     *            Example: true
     *      - 'relations': array (optional) Generate entities for relations.
     *            Example: ['users_ids' => ['qty' => 2]]
     *                      -> Keys are relation field names and values are Factory options.
     * @return array
     * @throws Exception
     */
    public static function create(string $class, array $options = []): array {
        /** @var ObjectManager $orm */
        ['orm' => $orm] = \eQual::inject(['orm']);

        $model = $orm->getModel($class);
        if(!$model) {
            throw new Exception('unknown_entity', EQ_ERROR_INVALID_PARAM);
        }

        $qty = self::extractQtyFromOptions($options);
        $sequences = self::extractSequencesFromOptions($options);
        $relations = self::extractRelationsFromOptions($options);
        $should_be_unique = self::extractUniqueFromOptions($options);

        $entities = [];

        $sequence_index = 0;
        $values = !empty($sequences) ? $sequences[0] : [];
        for($i = 1; $i <= $qty; $i++) {
            if(!$should_be_unique) {
                $entities[] = self::createEntityFromModel($model, $values, $relations);
            }
            else {
                try {
                    $entities[] = self::createUniqueEntityFromModel($entities, $model, $values, $relations);
                } catch(Exception $e) {
                    trigger_error("PHP::skip creation of $class because not able to create a valid unique entity.", EQ_REPORT_WARNING);
                }
            }

            if(!empty($sequences)) {
                $sequence_index = isset($sequences[++$sequence_index]) ? $sequence_index : 0;

                $values = $sequences[$sequence_index];
            }
        }

        $one_entity_wanted = ($options['qty'] ?? 1) === 1;

        return $one_entity_wanted ? $entities[0] : $entities;
    }

    private static function extractQtyFromOptions(array $options): int {
        $qty = 1;

        if(isset($options['qty'])) {
            if(is_array($options['qty']) && count($options['qty']) === 2 && is_int($options['qty'][0]) && is_int($options['qty'][1])) {
                $qty = mt_rand($options['qty'][0], $options['qty'][1]);
            }
            elseif(is_int($options['qty'])) {
                $qty = $options['qty'];
            }
            else {
                throw new Exception('invalid_option_qty', EQ_ERROR_INVALID_PARAM);
            }
        }

        return $qty;
    }

    private static function extractSequencesFromOptions(array $options): array {
        $sequences = [];

        if(empty($options['sequences']) && !empty($options['values'])) {
            $options['sequences'] = [$options['values']];
        }

        if(!empty($options['sequences'])) {
            foreach($options['sequences'] as $index => $values) {
                if(!is_int($index)) {
                    throw new Exception('invalid_option_sequences_index_must_be_integer', EQ_ERROR_INVALID_PARAM);
                }

                foreach($values as $field => $value) {
                    if(!is_string($field)) {
                        throw new Exception('invalid_option_sequences_field_must_be_a_string', EQ_ERROR_INVALID_PARAM);
                    }
                    if(is_array($value) || is_object($value)) {
                        throw new Exception('invalid_option_sequences_not_expected_value', EQ_ERROR_INVALID_PARAM);
                    }
                }
            }

            $sequences = $options['sequences'];
        }

        return $sequences;
    }

    private static function extractRelationsFromOptions(array $options): array {
        $relations = [];
        if(!empty($options['relations'])) {
            foreach($options['relations'] as $field => $factory_options) {
                if(is_int($field) && is_string($factory_options)) {
                    continue;
                }

                if(!is_string($field) || !is_array($factory_options)) {
                    throw new Exception('invalid_option_relations', EQ_ERROR_INVALID_PARAM);
                }
            }

            $relations = $options['relations'];
        }

        return $relations;
    }

    private static function extractUniqueFromOptions(array $options): bool {
        if(isset($options['unique']) && !is_bool($options['unique'])) {
            throw new Exception('invalid_option_unique', EQ_ERROR_INVALID_PARAM);
        }

        return $options['unique'] ?? true;
    }

    private static function createEntityFromModel(Model $model, array $forced_values = [], array $relations = []): array {
        $entity = [];
        foreach($model->getSchema() as $field => $field_descriptor) {
            $field_type = $field_descriptor['result_type'] ?? $field_descriptor['type'];
            if(array_key_exists($field, $forced_values)) {
                $entity[$field] = $forced_values[$field];
                continue;
            }
            elseif(
                in_array($field_type, self::$relationship_field_types)
                && (in_array($field, $relations) || array_key_exists($field, $relations))
            ) {
                if($field_type === 'many2one') {
                    $factory_options = $relations[$field] ?? [];
                    if(isset($factory_options['qty']) && $factory_options['qty'] !== 1) {
                        $factory_options['qty'] = 1;
                    }

                    $entity[$field] = self::create($field_descriptor['foreign_object'], $factory_options);
                } else {
                    $relation_entities = self::create($field_descriptor['foreign_object'], $relations[$field] ?? []);
                    if(!isset($relation_entities[0])) {
                        // If only one item returned put it in an array
                        $relation_entities = [$relation_entities];
                    }

                    $entity[$field] = $relation_entities;
                }
            }

            if(
                isset($entity[$field])
                || in_array($field, self::$root_fields)
                || in_array($field_type, self::$relationship_field_types)
                || in_array($field_descriptor['type'], ['computed', 'alias'])
            ) {
                continue;
            }

            $is_required = $field_descriptor['required'] ?? false;
            if(!$is_required && DataGenerator::boolean(0.05)) {
                $entity[$field] = null;
            }
            else {
                $entity[$field] = DataGenerator::generateFromField($field, $field_descriptor);
            }
        }

        return $entity;
    }

    /**
     * @throws Exception
     */
    private static function createUniqueEntityFromModel(array $other_entities, Model $model, array $forced_values = [], array $relations = []): array {
        $schema = $model->getSchema();
        $model_unique_conf = $model->getUnique();

        $count = 10;
        while($count > 0) {
            $entity = self::createEntityFromModel($model, $forced_values, $relations);

            $unique_valid = true;
            foreach($other_entities as $ent) {
                foreach($schema as $field => $field_descriptor) {
                    $field_should_be_unique = $field_descriptor['unique'] ?? false;
                    if(!$field_should_be_unique && !empty($model_unique_conf)) {
                        foreach($model_unique_conf as $unique_conf) {
                            if(count($unique_conf) === 1 && $unique_conf[0] === $field) {
                                $field_should_be_unique = true;
                                break;
                            }
                        }
                    }

                    if($field_should_be_unique && $ent[$field] === $entity[$field]) {
                        $unique_valid = false;
                        break 2;
                    }
                }
            }

            if($unique_valid) {
                return $entity;
            }

            $count--;
        }

        throw new Exception('not_able_to_generate_unique_valid_entity', EQ_ERROR_CONFLICT_OBJECT);
    }
}
