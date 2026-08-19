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
    'name'          =>  'test_package_i18n-consistency',
    'package_name'  =>  'core',
    'description'   =>  'Test package translation files and model translation references.',
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

$get_model_schema = function(string $class_name) use($orm): ?array {
    if(!class_exists($class_name) || !is_subclass_of($class_name, 'equal\orm\Model')) {
        return null;
    }

    $model = $orm->getModel($class_name);

    if(!$model || !is_object($model)) {
        return null;
    }

    $schema = $model->getSpecialColumns();
    $stack_classes = [$model->getType()];

    $root_parent = get_parent_class($model);
    while($root_parent && $root_parent != 'equal\orm\Model') {
        $stack_classes[] = $root_parent;
        $root_parent = get_parent_class($root_parent);
    }

    if(!$root_parent) {
        return null;
    }

    foreach(array_reverse($stack_classes) as $item) {
        $schema = array_merge($schema, $item::getColumns());
    }

    return $schema;
};

// result of the tests : array containing errors (if no errors are found, array is empty)
$result = [];

// get classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);

$lang_dir = "packages/{$params['package']}/i18n";

$lang_list = [];
if(is_dir($lang_dir) && ($list = scandir($lang_dir))) {
    foreach($list as $node) {
        if(is_dir($lang_dir.'/'.$node) && !in_array($node, ['.', '..'])) {
            $lang_list[] = $node;
        }
    }
}

foreach($classes as $class) {
    // get the full class name
    $class_name = $params['package'].'\\'.$class;
    $schema = $get_model_schema($class_name);
    if(is_null($schema)) {
        continue;
    }

    // 4) check if translation file are present (.json)

    foreach($lang_list as $lang) {
        try {
            eQual::run('get', 'config_i18n', ['entity' => $class_name, 'lang' => $lang]);
        }
        catch(Exception $e) {
            $result[] = "WARN  - I18 - Class $class: missing translation file for language $lang";
        }
    }

    // 6) check translation file consistency (.json)

    foreach($lang_list as $lang) {
        $i18n_file = "$lang_dir/$lang/$class.json";
        if(is_file($i18n_file)) {
            $json = file_get_contents($i18n_file);
            $data = @json_decode($json, true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                $result[] = "ERROR - I18 - Syntax error in file: $i18n_file";
                continue;
            }
            $mandatory_properties = ['name', 'plural', 'description', 'model', 'view'];
            foreach($mandatory_properties as $property) {
                if(!isset($data[$property])) {
                    $result[] = "ERROR - I18 - Missing mandatory property '$property' in file: $i18n_file";
                }
            }
            if(isset($data['model'])) {
                $fields = array_keys($data['model']);
                // check that the referenced fields are valid (defined in the schema)
                foreach($fields as $field) {
                    if(!isset($schema[$field])) {
                        $result[] = "WARN  - I18 - Unknown field '$field' referenced in file $i18n_file";
                    }
                    // warn about renaming root fields (special fields from Model interface)
                    if(in_array($field, ['id', 'creator', 'modifier', 'modified','created', 'deleted', 'state'])) {
                        $result[] = "WARN  - I18 - Root field '$field' shouldn't be referenced in file $i18n_file";
                    }
                }
                // check that the translation description is complete for each field
                foreach($fields as $field) {
                    $mandatory_properties = ['label', 'help', 'description'];
                    foreach($mandatory_properties as $property) {
                        if(!isset($data['model'][$field][$property])) {
                            $result[] = "WARN  - I18 - Missing property '$property' for field '$field' referenced in file $i18n_file";
                        }
                        else {
                            if(mb_strlen($data['model'][$field][$property]) == 0) {
                                $result[] = "WARN  - I18 - Value for property '$property' should not be empty for field '$field' referenced in file $i18n_file";
                                continue;
                            }
                            if(!preg_match('/^\p{Lu}/u', $data['model'][$field][$property])) {
                                $result[] = "WARN  - I18 - Value for property '$property' should start with uppercase for field '$field' referenced in file $i18n_file";
                            }
                            if($property == 'label') {
                                if(mb_strlen($data['model'][$field][$property]) && substr($data['model'][$field][$property], -1) == '.') {
                                    $result[] = "WARN  - I18 - Value for property '$property' should not end by '.' for field '$field' referenced in file $i18n_file";
                                }
                            }
                            elseif($property == 'help') {
                                if(mb_strlen($data['model'][$field][$property]) && !in_array(substr($data['model'][$field][$property], -1), ['.', '?', '!'])) {
                                    $result[] = "WARN  - I18 - Value for property '$property' should end by '.' for field '$field' referenced in file $i18n_file";
                                }
                            }
                            elseif($property == 'description') {
                                if(mb_strlen($data['model'][$field][$property])) {
                                    if(!in_array(substr($data['model'][$field][$property], -1), ['.', '?', '!'])) {
                                        $result[] = "WARN  - I18 - Value for property '$property' should end by '.' for field '$field' referenced in file $i18n_file";
                                    }
                                    if(mb_strlen($data['model'][$field][$property]) > 65) {
                                        $result[] = "WARN  - I18 - Property '$property' should not exceed 65 chars for field '$field' referenced in file $i18n_file";
                                    }
                                }
                            }
                        }
                    }
                    // check for 'selection' property
                    if(isset($schema[$field]) && $schema[$field]['type'] == 'string' && isset($schema[$field]['selection'])) {
                        if(!isset($data['model'][$field]['selection'])) {
                            $result[] = "WARN  - I18 - Missing property 'selection' for field '$field' referenced in file $i18n_file";
                        }
                    }
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
