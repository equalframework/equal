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

    // create reflection class to allow checks on class
    $refClass = new ReflectionClass($class_name);

    // check PHP syntax
    $output = shell_exec(sprintf('php -l %s 2>&1', escapeshellarg($class_filename)));
    if(!preg_match('!No syntax errors detected!', $output)) {
        throw new Exception("FATAL - syntax error found in '{$class_name}' class definition file: {$output} ", QN_ERROR_UNKNOWN);
    }

    if(!class_exists($class_name, false)) {
        include_once $class_filename;
    }

    if(!class_exists($class_name, false)) {
        $result[] = "ERROR - ORM - unknown class '{$class_name}'";
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

    // #toto - fields involved in unique constraint should be set as required

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
            if(in_array($attribute, ['store', 'instant', 'multilang', 'readonly']) && $value !== true && $value !== false) {
                $result[] = "ERROR - ORM - Class $class_name: Incompatible value for attribute $attribute in field $field of type {$descriptor['type']} (possible attributes are : true, false) ($class_filename)";
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
            if(!isset($relation_full_schema[$descriptor['order']])) {
                $result[] = "ERROR - ORM - Class $class_name: Invalid order '{$descriptor['order']}' for field $field ($class_filename)";
            }
        }
        if(isset($descriptor['ondelete']) && !in_array($descriptor['ondelete'], ['cascade', 'null'])) {
            $result[] = "ERROR - ORM - Class $class_name: Invalid ondelete '{$descriptor['ondelete']}' for field $field ($class_filename)";
        }
        if(isset($descriptor['ondetach']) && !in_array($descriptor['ondetach'], ['delete', 'null'])) {
            $result[] = "ERROR - ORM - Class $class_name: Invalid ondetach '{$descriptor['ondetach']}' for field $field ($class_filename)";
        }
    }

    $uniques = $model->getUnique();
    foreach($uniques as $unique_fields) {
        foreach($unique_fields as $ufield) {
            if(!isset($schema[$ufield])) {
                $result[] = "ERROR - ORM - Class $class: Non-existing field '{$ufield}' given in unique constraints";
            }
        }
    }

    // #todo - 2) check presence of class definition files to which some field may be related to + domain validity (ref to other object), when present

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
                    if(!is_array($descriptor['relation'])) {
                        $result[] = "ERROR - ORM - Class $class_name: Computed field $field relation must be an array ($class_filename)";
                    }
                    elseif(count($descriptor['relation']) !== 1 || !is_string(array_keys($descriptor['relation'])[0]) || !is_string(array_values($descriptor['relation'])[0])) {
                        $result[] = "ERROR - ORM - Class $class_name: Computed field $field relation must be a valid array like '".json_encode(['user_id' => 'name'])."' ($class_filename)";
                    }
                    else {
                        $obj_field = array_keys($descriptor['relation'])[0];
                        $foreign_field = array_values($descriptor['relation'])[0];
                        if(!isset($schema[$obj_field]['foreign_object'])) {
                            $result[] = "ERROR - ORM - Class $class_name: Computed field $field relation has an unknown field '$obj_field' ($class_filename)";
                        }
                        else {
                            $relationModel = $orm->getModel($schema[$obj_field]['foreign_object']);
                            $relation_full_schema = $relationModel->getSchema();
                            if(!isset($relation_full_schema[$foreign_field])) {
                                $result[] = "ERROR - ORM - Class $class_name: Computed field $field relation has an unknown field '$obj_field.$foreign_field' ($class_filename)";
                            }
                        }
                    }
                }
                break;
        }

        if(isset($descriptor['dependents'])) {
            foreach($descriptor['dependents'] as $key => $value) {
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
