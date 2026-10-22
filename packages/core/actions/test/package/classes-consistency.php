<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

// get listing of existing packages
$packages = eQual::run('get', 'config_packages');

// announce script and fetch parameters values
[$params, $providers] = eQual::announce([
    'type'          =>  'do',
    'name'          =>  'test_package_classes-consistency',
    'package_name'  =>  'core',
    'description'   =>  'Test PHP classes and ORM model definitions for a package.',
    'params'        =>  [
        'package'   =>  [
            'description'   => 'Package to validate.',
            'type'          => 'string',
            'selection'     => array_combine(array_values($packages), array_values($packages)),
            'required'      => true
        ],
        'level' =>  [
            'description'   => 'Level of notification to limit the output to.',
            'type'          => 'string',
            'selection'     => ['*', 'warn', 'error'],
            'default'       => '*'
        ],
        'strict'    =>  [
            'description'   => 'Flag to enable strict mode.',
            'type'          => 'boolean',
            'default'       => false
        ],
        'exit_on_error' => [
            'description'   => 'Exit with a non-zero status code when consistency errors are found.',
            'type'          => 'boolean',
            'default'       => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
[$context, $orm] = [$providers['context'], $providers['orm']];

$filter_result = function(array $result, string $level): array {
    foreach($result as $index => $line) {
        if(strpos($line, 'ERROR') === 0 && !in_array($level, ['*', 'error'])) {
            unset($result[$index]);
            continue;
        }
        if(strpos($line, 'WARN') === 0 && !in_array($level, ['*', 'warn'])) {
            unset($result[$index]);
            continue;
        }
    }
    return array_values($result);
};

$count_result = function(array $result): array {
    $counts = ['errors' => 0, 'warnings' => 0];
    foreach($result as $line) {
        if(strpos($line, 'ERROR') === 0) {
            ++$counts['errors'];
        }
        elseif(strpos($line, 'WARN') === 0) {
            ++$counts['warnings'];
        }
    }
    return $counts;
};

$is_value_compatible_with_type = function($value, string $type): bool {
    if(is_null($value)) {
        return true;
    }

    switch($type) {
        case 'boolean':
            return is_bool($value);
        case 'integer':
        case 'date':
        case 'time':
        case 'datetime':
        case 'many2one':
            return is_int($value);
        case 'float':
            return is_float($value) || is_int($value);
        case 'string':
        case 'text':
        case 'binary':
        case 'file':
            return is_string($value);
        case 'one2many':
        case 'many2many':
            return is_array($value);
    }

    return true;
};

$validate_domain = function($domain, array $schema) use($orm): ?string {
    if(!is_array($domain)) {
        return 'must be an array';
    }
    if(empty($domain)) {
        return null;
    }
    if(!array_key_exists(0, $domain)) {
        return 'contains an invalid clause';
    }

    if(!is_array($domain[0])) {
        $domain = [[$domain]];
    }
    elseif(empty($domain[0])) {
        return null;
    }
    elseif(!array_key_exists(0, $domain[0])) {
        return 'contains an invalid condition';
    }
    elseif(!is_array($domain[0][0])) {
        $domain = [$domain];
    }

    foreach($domain as $clause) {
        if(!is_array($clause) || empty($clause)) {
            return 'contains an invalid clause';
        }
        foreach($clause as $condition) {
            if(!is_array($condition) || array_keys($condition) !== [0, 1, 2]) {
                return 'contains a condition that must have exactly three values';
            }

            [$condition_field, $operator] = $condition;
            if(!is_string($condition_field) || !isset($schema[$condition_field])) {
                return "references unknown field '".(is_scalar($condition_field) ? $condition_field : gettype($condition_field))."'";
            }
            if(!is_string($operator)) {
                return 'contains a non-string operator';
            }

            $condition_descriptor = $schema[$condition_field];
            $visited_aliases = [];
            while(($condition_descriptor['type'] ?? null) === 'alias') {
                $alias = $condition_descriptor['alias'] ?? null;
                if(!is_string($alias) || !isset($schema[$alias]) || isset($visited_aliases[$alias])) {
                    return "references invalid alias '$condition_field'";
                }
                $visited_aliases[$alias] = true;
                $condition_descriptor = $schema[$alias];
            }

            $condition_type = $condition_descriptor['result_type'] ?? $condition_descriptor['type'] ?? null;
            if(!is_string($condition_type) || !isset($orm::$valid_operators[$condition_type])) {
                return "uses unsupported field type '$condition_type'";
            }
            if(!in_array($operator, $orm::$valid_operators[$condition_type], true)) {
                return "uses invalid operator '$operator' for field '$condition_field'";
            }
        }
    }

    return null;
};

$validate_relation = function($relation, array $schema, string $path='') use(&$validate_relation, $orm): ?string {
    if(!is_array($relation) || count($relation) !== 1) {
        return 'must be a non-empty single-item array';
    }

    $key = array_key_first($relation);
    $target = $relation[$key];

    if(is_int($key)) {
        if(!is_string($target)) {
            return 'must end with a field name';
        }

        $target_path = strlen($path) ? "$path.$target" : $target;
        if(!isset($schema[$target])) {
            return "references unknown field '$target_path'";
        }
        return null;
    }

    if(!is_string($key) || !strlen($key)) {
        return 'contains an invalid relation field';
    }

    $relation_path = strlen($path) ? "$path.$key" : $key;
    if(!isset($schema[$key])) {
        return "references unknown field '$relation_path'";
    }
    if(empty($schema[$key]['foreign_object']) || !is_string($schema[$key]['foreign_object'])) {
        return "uses non-relational field '$relation_path' before the end of the path";
    }

    $foreign_object = $schema[$key]['foreign_object'];
    if(!class_exists($foreign_object) || !is_subclass_of($foreign_object, 'equal\\orm\\Model')) {
        return "references unknown entity '$foreign_object' through field '$relation_path'";
    }

    try {
        $relation_model = $orm->getModel($foreign_object);
        if(!$relation_model || !is_object($relation_model)) {
            return "cannot resolve relation target for field '$relation_path'";
        }
        $relation_schema = $relation_model->getSchema();
    }
    catch(Throwable) {
        return "cannot resolve relation target for field '$relation_path'";
    }

    if(is_string($target)) {
        $target_path = "$relation_path.$target";
        if(!isset($relation_schema[$target])) {
            return "references unknown field '$target_path'";
        }
        return null;
    }
    if(!is_array($target)) {
        return "contains an invalid target after field '$relation_path'";
    }

    return $validate_relation($target, $relation_schema, $relation_path);
};

// result of the tests : array containing errors (if no errors are found, array is empty)
$result = [];

// get classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);

foreach($classes as $class) {
    // get the full class name
    $class_name = $params['package'].'\\'.$class;
    // get the related filename
    $class_filename = str_replace('\\', '/', "packages/{$params['package']}/classes/{$class}".'.class.php');
    // check file existence
    if(!file_exists($class_filename)) {
        throw new Exception("FATAL - class definition file missing for '{$class_name}' ", QN_ERROR_UNKNOWN_OBJECT);
    }

    // check PHP syntax
    $output = shell_exec(sprintf('php -l %s 2>&1', escapeshellarg($class_filename)));
    if(!preg_match('!No syntax errors detected!', $output)) {
        throw new Exception("FATAL - syntax error found in '{$class_name}' class definition file: {$output} ", QN_ERROR_UNKNOWN);
    }

    if(!class_exists($class_name, false)) {
        include_once $class_filename;
    }

    if(!class_exists($class_name, false)) {
        $result[] = "ERROR - ORM - Class declaration does not match expected namespace and class '{$class_name}' for file $class_filename";
        continue;
    }

    // create reflection class to allow checks on class
    $refClass = new ReflectionClass($class_name);
    $refClassFilename = $refClass->getFileName();
    if($refClass->getName() !== $class_name || !$refClassFilename || realpath($refClassFilename) !== realpath($class_filename)) {
        $result[] = "ERROR - ORM - Class '{$refClass->getName()}' namespace, name, or source file does not match expected class '$class_name' and path $class_filename";
        continue;
    }

    if(!is_subclass_of($class_name, 'equal\orm\Model')) {
        $result[] = "ERROR - ORM - Class $class_name does not inherit from `equal\orm\Model` root class.";
        continue;
    }

    if($class_name::isAbstract()) {
        continue;
    }

    // #todo - an Exception may still arise while loading a class dependency (of the same package)
    $model = $orm->getModel($class_name);

    if(!$model || !is_object($model)) {
        $result[] = "ERROR - ORM - unknown class '{$class_name}'";
        continue;
    }

    // get the complete schema of the object (including special fields)
    // #memo - we want the fields as they are defined in the class, not as they are returned by getSchema()
    $schema = $model->getSpecialColumns();
    $stack_classes = [$model->getType()];

    // verify that the class actually inherits from Model
    // retrieve root class (before Model)
    $root_parent = get_parent_class($model);
    while($root_parent && $root_parent != 'equal\orm\Model') {
        $stack_classes[] = $root_parent;
        $root_parent = get_parent_class($root_parent);
    }

    if(!$root_parent ) {
        throw new Exception("FATAL - ORM - Class $class_name does not inherit from `equal\orm\Model` root class.", QN_ERROR_UNKNOWN_OBJECT);
    }

    foreach(array_reverse($stack_classes) as $item) {
        $schema = array_merge($schema, $item::getColumns());
    }

    // 1) check fields descriptors consistency

    $valid_types = array_merge($orm::$virtual_types, $orm::$simple_types, $orm::$complex_types);

    $boolean_attributes = ['instant', 'multilang', 'readonly', 'required', 'sensitive', 'store', 'unique'];

    foreach($schema as $field => $descriptor) {
        if(!isset($descriptor['type'])) {
            $result[] = "ERROR - ORM - Class $class: Missing 'type' attribute for field $field ($class_filename)";
            continue;
        }
        if(!in_array($descriptor['type'], $valid_types)) {
            $result[] = "ERROR - ORM - Class $class: Invalid type '{$descriptor['type']}' for field $field ($class_filename)";
            continue;
        }
        if(!isset($orm::$mandatory_attributes[$descriptor['type']])) {
            $result[] = "ERROR - ORM - Missing mandatory attributes definition for type {$descriptor['type']}";
            continue;
        }
        if(!$orm::checkFieldAttributes($orm::$mandatory_attributes, $schema, $field)) {
            $result[] = "ERROR - ORM - Class $class_name: Missing at least one mandatory attribute for field '$field' ({$descriptor['type']}) - mandatory attributes are : ".json_encode($orm::$mandatory_attributes[$descriptor['type']])." ($class_filename)";
            continue;
        }
        foreach($descriptor as $attribute => $value) {
            if(!isset($orm::$valid_attributes[$descriptor['type']])) {
                $result[] = "ERROR - ORM - Missing valid attributes definition for type {$descriptor['type']}";
                continue;
            }
            if(!in_array($attribute, $orm::$valid_attributes[$descriptor['type']])) {
                $result[] = "WARN  - ORM - Class $class_name: Unknown attribute '$attribute' for field '$field' ({$descriptor['type']}) - Possible attributes are : ".implode(', ', $orm::$valid_attributes[$descriptor['type']])." ($class_filename)";
            }
            if(in_array($attribute, $boolean_attributes, true) && !is_bool($value)) {
                $result[] = "ERROR - ORM - Class $class_name: Incompatible value for attribute $attribute in field $field of type {$descriptor['type']} (possible attributes are : true, false) ($class_filename)";
            }
        }

        $effective_type = $descriptor['result_type'] ?? $descriptor['type'];
        if($descriptor['type'] === 'alias' && isset($descriptor['alias'], $schema[$descriptor['alias']])) {
            $target_descriptor = $schema[$descriptor['alias']];
            $effective_type = $target_descriptor['result_type'] ?? $target_descriptor['type'] ?? $effective_type;
        }

        $selection_values = null;
        if(array_key_exists('selection', $descriptor)) {
            if(!is_array($descriptor['selection']) || empty($descriptor['selection'])) {
                $result[] = "ERROR - ORM - Class $class_name: Selection for field $field must be a non-empty array ($class_filename)";
            }
            else {
                $selection = $descriptor['selection'];
                $is_list = array_keys($selection) === range(0, count($selection) - 1);
                $selection_values = $is_list ? array_values($selection) : array_keys($selection);

                foreach($selection_values as $index => $selection_value) {
                    if(!$is_value_compatible_with_type($selection_value, $effective_type)) {
                        $result[] = "ERROR - ORM - Class $class_name: Selection value at index $index for field $field is incompatible with type $effective_type ($class_filename)";
                    }
                }

                if(!$is_list) {
                    foreach($selection as $selection_label) {
                        if(!is_string($selection_label)) {
                            $result[] = "ERROR - ORM - Class $class_name: Associative selection labels for field $field must be strings ($class_filename)";
                            break;
                        }
                    }
                }

                for($i = 0, $count = count($selection_values); $i < $count; ++$i) {
                    for($j = $i + 1; $j < $count; ++$j) {
                        if($selection_values[$i] === $selection_values[$j]) {
                            $result[] = "ERROR - ORM - Class $class_name: Selection for field $field contains duplicate values ($class_filename)";
                            break 2;
                        }
                    }
                }
            }
        }

        if(array_key_exists('default', $descriptor) && !is_null($descriptor['default']) && !($descriptor['default'] instanceof Closure)) {
            $default = $descriptor['default'];
            $is_dynamic_default = is_string($default)
                && ($default === 'defaultFromSetting' || method_exists($class_name, $default));
            if(!$is_dynamic_default && !$is_value_compatible_with_type($default, $effective_type)) {
                $result[] = "ERROR - ORM - Class $class_name: Default value for field $field is incompatible with type $effective_type ($class_filename)";
            }
            elseif(!$is_dynamic_default && is_array($selection_values) && !in_array($default, $selection_values, true)) {
                $result[] = "ERROR - ORM - Class $class_name: Default value for field $field is not present in its selection ($class_filename)";
            }
        }

        if(array_key_exists('pattern', $descriptor)) {
            if(!is_string($descriptor['pattern']) || @preg_match($descriptor['pattern'], '') === false) {
                $result[] = "ERROR - ORM - Class $class_name: Invalid regular expression pattern for field $field ($class_filename)";
            }
        }

        if(isset($descriptor['foreign_object']) && !class_exists($descriptor['foreign_object']))  {
            $result[] = "ERROR - ORM - Class $class_name: Non-existing entity '{$descriptor['foreign_object']}' given for attribute foreign_object in field $field of type {$descriptor['type']} ($class_filename)";
        }
        if(isset($descriptor['foreign_field'])) {
            $relationModel = $orm->getModel($descriptor['foreign_object']);
            $relation_full_schema = $relationModel->getSchema();
            if(!isset($relation_full_schema[$descriptor['foreign_field']])) {
                $result[] = "ERROR - ORM - Class $class_name: Invalid foreign_field '{$descriptor['foreign_field']}' for field $field ($class_filename)";
            }
        }
        if(isset($descriptor['alias'])) {
            if(!isset($schema[$descriptor['alias']])) {
                $result[] = "ERROR - ORM - Class $class_name: Unknown alias '{$descriptor['alias']}' for field $field ($class_filename)";
            }
            elseif($descriptor['alias'] === $field) {
                $result[] = "ERROR - ORM - Class $class_name: Self referencing alias '{$descriptor['alias']}' for field $field ($class_filename)";
            }
        }
        if(isset($descriptor['sort']) && !in_array($descriptor['sort'], ['asc', 'desc'])) {
            $result[] = "ERROR - ORM - Class $class_name: Invalid sort '{$descriptor['sort']}' for field $field ($class_filename)";
        }
        if(isset($descriptor['order'])) {
            $relationModel = $orm->getModel($descriptor['foreign_object']);
            $relation_full_schema = $relationModel->getSchema();
            $order_fields = explode(',', $descriptor['order']);
            foreach($order_fields as $order_field) {
                if(!isset($relation_full_schema[$order_field])) {
                    $result[] = "ERROR - ORM - Class $class_name: Invalid order '$order_field' for field $field ($class_filename)";
                }
            }
        }
        if(isset($descriptor['ondelete']) && !in_array($descriptor['ondelete'], ['cascade', 'null'])) {
            $result[] = "ERROR - ORM - Class $class_name: Invalid ondelete '{$descriptor['ondelete']}' for field $field ($class_filename)";
        }
        if(isset($descriptor['ondetach']) && !in_array($descriptor['ondetach'], ['delete', 'null'])) {
            $result[] = "ERROR - ORM - Class $class_name: Invalid ondetach '{$descriptor['ondetach']}' for field $field ($class_filename)";
        }

        foreach(['domain', 'visible'] as $domain_attribute) {
            if(!array_key_exists($domain_attribute, $descriptor)) {
                continue;
            }
            if($domain_attribute === 'visible' && is_bool($descriptor[$domain_attribute])) {
                continue;
            }

            $domain_schema = $schema;
            if($domain_attribute === 'domain') {
                if(!isset($descriptor['foreign_object']) || !class_exists($descriptor['foreign_object'])) {
                    $result[] = "ERROR - ORM - Class $class_name: Domain for field $field requires a valid foreign_object ($class_filename)";
                    continue;
                }
                $domain_schema = $orm->getModel($descriptor['foreign_object'])->getSchema();
            }

            $domain_error = $validate_domain($descriptor[$domain_attribute], $domain_schema);
            if(!is_null($domain_error)) {
                $result[] = "ERROR - ORM - Class $class_name: Invalid $domain_attribute for field $field: $domain_error ($class_filename)";
            }
        }
    }

    $getUniqueMethod = new ReflectionMethod($class_name, 'getUnique');
    if($getUniqueMethod->getDeclaringClass()->getName() === $class_name) {
        $result[] = "WARN  - ORM - Class $class_name: Usage of deprecated method 'getUnique()'; use 'getUniques()' instead ($class_filename)";
    }

    $uniques = $model->getUnique();
    if(!is_array($uniques)) {
        $result[] = "ERROR - ORM - Class $class_name: Unique constraints must be an array ($class_filename)";
        $uniques = [];
    }
    foreach($uniques as $unique_fields) {
        if(!is_array($unique_fields) || empty($unique_fields)) {
            $result[] = "ERROR - ORM - Class $class_name: Each unique constraint must be a non-empty array of fields ($class_filename)";
            continue;
        }
        foreach($unique_fields as $ufield) {
            if(!is_string($ufield) || !isset($schema[$ufield])) {
                $result[] = "ERROR - ORM - Class $class_name: Non-existing field '".(is_scalar($ufield) ? $ufield : gettype($ufield))."' given in unique constraints ($class_filename)";
            }
        }
    }

    // 2) check consistency of the complete schema
    $full_schema = $model->getSchema();
    foreach($full_schema as $field => $descriptor) {
        foreach(['onupdate', 'onrevert'] as $callback_attribute) {
            if(!isset($descriptor[$callback_attribute])) {
                continue;
            }

            $parts = explode('::', $descriptor[$callback_attribute]);
            $count = count((array) $parts);

            $called_class = $class_name;
            $called_method = $descriptor[$callback_attribute];

            if($count < 1 || $count > 2) {
                $result[] = "ERROR - ORM - Class $class_name: Field $field has invalid $callback_attribute property ({$descriptor[$callback_attribute]})";
            }
            else {
                if($count == 2) {
                    $called_class = $parts[0];
                    $called_method = $parts[1];
                }
                if(!method_exists($called_class, $called_method)) {
                    $result[] = "ERROR - ORM - Class $class_name: Field $field has $callback_attribute property with unknown handler '{$descriptor[$callback_attribute]}'";
                }
            }
        }

        if(isset($descriptor['description'])) {
            if(mb_strlen($descriptor['description'])) {
                if(!preg_match('/^\p{Lu}/u', $descriptor['description'])) {
                    $result[] = "WARN  - ORM - Value for attribute 'description' should start with uppercase for field '$field' referenced in file $class_filename";
                }
                if(!in_array(substr($descriptor['description'], -1), ['.', '?', '!'])) {
                    $result[] = "WARN  - ORM - Value for attribute 'description' should end by '.' for field '$field' referenced in file $class_filename";
                }
                if(mb_strlen($descriptor['description']) > 65) {
                    $result[] = "WARN  - ORM - Value for attribute 'description' should not exceed 65 chars for field '$field' referenced in file $class_filename";
                }
            }
        }

        switch($descriptor['type']) {
            case 'computed':
                if(!isset($descriptor['result_type'])) {
                    $result[] = "ERROR - ORM - Class $class_name: Computed field $field is missing 'result_type' attribute ($class_filename)";
                }
                elseif(!in_array($descriptor['result_type'], $orm::$simple_types)) {
                    $result[] = "ERROR - ORM - Class $class_name: Computed field $field result_type '{$descriptor['result_type']}' is invalid ($class_filename)";
                }
                if(!isset($descriptor['function']) && !isset($descriptor['relation'])) {
                    $result[] = "ERROR - ORM - Class $class_name: Computed field $field is missing one of the 'function' and 'relation' attributes ($class_filename)";
                }
                elseif(isset($descriptor['function']) && isset($descriptor['relation'])) {
                    $result[] = "ERROR - ORM - Class $class_name: Computed field $field cannot use the 'function' and 'relation' attributes together ($class_filename)";
                }
                elseif(isset($descriptor['function'])) {
                    if(!is_string($descriptor['function'])) {
                        $result[] = "ERROR - ORM - Class $class_name: Computed field $field function must be a string ($class_filename)";
                    }
                    if(!$refClass->hasMethod($descriptor['function'])) {
                        $result[] = "ERROR - ORM - Class $class_name: Computed field $field function '{$descriptor['function']}' doesn't exist ($class_filename)";
                    }
                    else {
                        $method = $refClass->getMethod($descriptor['function']);
                        if(!$method->isStatic()) {
                            $result[] = "ERROR - ORM - Class $class_name: Computed field $field function '{$descriptor['function']}' is not static ($class_filename)";
                        }
                        elseif(!$method->isProtected()) {
                            $result[] = "WARN  - ORM - Class $class_name: Computed field $field function '{$descriptor['function']}' is not protected ($class_filename)";
                        }
                        else {
                            foreach($method->getParameters() as $paramConf) {
                                $param_name = $paramConf->getName();
                                if(!in_array($param_name, $orm::$valid_inject_params)) {
                                    $result[] = "ERROR - ORM - Class $class_name: Computed field $field function '{$descriptor['function']}' has an invalid parameter '$param_name' ($class_filename)";
                                }
                            }
                        }
                    }
                }
                elseif(isset($descriptor['relation'])) {
                    $relation_error = $validate_relation($descriptor['relation'], $schema);
                    if(!is_null($relation_error)) {
                        $result[] = "ERROR - ORM - Class $class_name: Computed field $field relation $relation_error ($class_filename)";
                    }
                }
                break;
        }

        if(isset($descriptor['dependents'])) {
            foreach($descriptor['dependents'] as $key => $value) {
                if(is_string($key) && is_string($value)) {
                    $value = [$value];
                }
                if(is_string($value)) {
                    if(!isset($full_schema[$value])) {
                        $result[] = "ERROR - ORM - Class $class_name: Field $field has unknown dependent '$value'";
                    }
                    elseif($full_schema[$value]['type'] !== 'computed') {
                        $result[] = "ERROR - ORM - Class $class_name: Field $field has dependent '$value' that isn't of computed type";
                    }
                }
                elseif(is_array($value)) {
                    if(!isset($full_schema[$key])) {
                        $result[] = "ERROR - ORM - Class $class_name: Field $field has unknown dependent '$key'";
                    }

                    foreach($value as $sub_value) {
                        if(is_string($sub_value)) {
                            if(!isset($full_schema[$key]) || empty($full_schema[$key]['foreign_object'])) {
                                $result[] = "ERROR - ORM - Class $class_name: Field $field has unknown dependent '$key.$sub_value'";
                            }
                            else {
                                $relationModel = $orm->getModel($full_schema[$key]['foreign_object']);
                                $relation_full_schema = $relationModel->getSchema();
                                if(!isset($relation_full_schema[$sub_value])) {
                                    $result[] = "ERROR - ORM - Class $class_name: Field $field has unknown dependent '$key.$sub_value'";
                                }
                                else {
                                    if(isset($relation_full_schema[$sub_value]['dependents'])) {
                                        foreach($relation_full_schema[$sub_value]['dependents'] as $k => $v) {
                                            if(is_array($v) && isset($relation_full_schema[$k]['foreign_object']) && $relation_full_schema[$k]['foreign_object'] === $class_name) {
                                                $result[] = "ERROR - ORM - Class $class_name: Field $field has circular dependency";
                                            }
                                        }
                                    }
                                    if($relation_full_schema[$sub_value]['type'] !== 'computed') {
                                        $result[] = "ERROR - ORM - Class $class_name: Field $field has dependent '$key.$sub_value' that isn't of computed type";
                                    }
                                }
                            }
                        }
                        else {
                            $result[] = "ERROR - ORM - Class $class_name: Field $field has misconfigured dependent '$sub_value'";
                        }
                    }
                }
                else {
                    $result[] = "ERROR - ORM - Class $class_name: Field $field has misconfigured dependent'";
                }
            }
        }

        if(isset($descriptor['dependencies'])) {
            $result[] = "WARN  - ORM - Usage of deprecated attribute 'dependencies' for field '$field'";
        }
    }
}

// filter result
$counts = $count_result($result);
$result = $filter_result($result, $params['level']);

if(!count($result)) {
    $result[] = 'INFO - Nothing to report.';
}

// send json result
$context->httpResponse()
        ->body([
            'result'   => $result,
            'errors'   => $counts['errors'],
            'warnings' => $counts['warnings']
        ])
        ->send();

// in case of error(s), force exiting with an error code
if($counts['errors'] && $params['exit_on_error']) {
    exit(1);
}
