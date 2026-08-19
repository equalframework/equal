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
    // #todo - check match between namespace and package
    // #todo - check match between classname and filename

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
        $result[] = "ERROR - ORM - unknown class '{$class_name}'";
        continue;
    }
    if(!is_subclass_of($class_name, 'equal\orm\Model')) {
        $result[] = "ERROR - ORM - Class $class_name does not inherit from `equal\orm\Model` root class.";
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
        if(!$orm::checkFieldAttributes($orm::$mandatory_attributes, $schema, $field)) {
            $result[] = "ERROR - ORM - Class $class: Missing at least one mandatory attribute for field '$field' ({$descriptor['type']}) - mandatory attributes are : ".json_encode($orm::$mandatory_attributes[$descriptor['type']])." ($class_filename)";
            continue;
        }
        foreach($descriptor as $attribute => $value) {
            if(!in_array($attribute, $orm::$valid_attributes[$descriptor['type']])) {
                $result[] = "WARN  - ORM - Class $class: Unknown attribute '$attribute' for field '$field' ({$descriptor['type']}) - Possible attributes are : ".implode(', ', $orm::$valid_attributes[$descriptor['type']])." ($class_filename)";
            }
            if(in_array($attribute, ['store', 'multilang', 'readonly']) && $value !== true && $value !== false) {
                $result[] = "ERROR - ORM - Class $class: Incompatible value for attribute $attribute in field $field of type {$descriptor['type']} (possible attributes are : true, false)"." ($class_filename)";
            }
            if($attribute == 'foreign_object' && !class_exists($value))  {
                $result[] = "ERROR - ORM - Class $class: Non-existing entity '{$value}' given for attribute $attribute in field $field of type {$descriptor['type']}"." ($class_filename)";
            }
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
        if(isset($descriptor['onupdate'])) {
            $parts = explode('::', $descriptor['onupdate']);
            $count = count((array) $parts);

            $called_class = $class_name;
            $called_method = $descriptor['onupdate'];

            if($count < 1 || $count > 2) {
                $result[] = "ERROR - ORM - Class $class_name: Field $field has invalid onupdate property ({$descriptor['onupdate']})";
            }
            else {
                if($count == 2) {
                    $called_class = $parts[0];
                    $called_method = $parts[1];
                }
                if(!method_exists($called_class, $called_method)) {
                    $result[] = "ERROR - ORM - Class $class_name: Field $field has onupdate property with unknown handler '{$descriptor['onupdate']}'";
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
