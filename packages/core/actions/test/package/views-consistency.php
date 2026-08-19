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
    'name'          =>  'test_package_views-consistency',
    'package_name'  =>  'core',
    'description'   =>  'Test package view definitions and model field references.',
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

$view_test = function($data, array $structure) use(&$view_test) {
    if(!is_array($data) || !count($data)) {
        return 0;
    }
    $sub_keys = array_keys($data);
    if(!is_numeric($sub_keys[0])) {
        $data = [$data];
    }
    foreach($data as $index => $elem) {
        if(empty($elem)) {
            continue;
        }
        foreach($structure as $item => $def) {
            $key = is_numeric($item) ? $def : $item;
            if(is_numeric($item)) {
                if(!isset($elem[$key])) {
                    if($key === 'width' && (isset($elem['visible']) || isset($elem['widget']['visible']))) {
                        // width is non-mandatory if the item is not visible
                        continue;
                    }
                    return "missing property '$key' for item $index";
                }
            }
            elseif(isset($elem[$key]) && isset($structure[$item])){
                $res = $view_test($elem[$key], $structure[$item]);
                if($res) {
                    return $res;
                }
            }
        }
    }
    return 0;
};

$view_get_items = function($data, array $structure) use(&$view_get_items): array {
    $result = [];
    if(!is_array($data) || !count($data)) {
        return $result;
    }
    $sub_keys = array_keys($data);
    if(!is_numeric($sub_keys[0])) {
        $data = [$data];
    }
    foreach($data as $elem) {
        foreach($structure as $item => $def) {
            if(!is_numeric($item)) {
                if($item == 'items') {
                    if(isset($elem[$item]) && is_array($elem[$item])) {
                        $result = array_merge($result, $elem[$item]);
                    }
                }
                elseif(isset($elem[$item])) {
                    $res = $view_get_items($elem[$item], $structure[$item]);
                    $result = array_merge($result, $res);
                }
            }
        }
    }
    return $result;
};

// result of the tests : array containing errors (if no errors are found, array is empty)
$result = [];

// get classes listing
$classes = eQual::run('get', 'config_classes', ['package' => $params['package']]);
$view_dir = "packages/{$params['package']}/views";

foreach($classes as $class) {
    // get the full class name
    $class_name = $params['package'].'\\'.$class;
    $schema = $get_model_schema($class_name);
    if(is_null($schema)) {
        continue;
    }

    // 3) check if default views are present (form.default.json and list.default.json)

    try {
        eQual::run('get', 'model_view', ['entity' => $class_name, 'view_id' => 'form.default']);
    }
    catch(Exception $e) {
        $result[] = "ERROR - GUI - Class $class: missing default form view (/views/$class.form.default.json)";
    }

    try {
        eQual::run('get', 'model_view', ['entity' => $class_name, 'view_id' => 'list.default']);
    }
    catch(Exception $e) {
        $result[] = "ERROR - GUI - Class $class: missing default list view (/views/$class.list.default.json)";
    }

    // 5) check view files consistency (.json)

    $view_files = glob("$view_dir/$class.*.json");

    foreach($view_files as $view_file) {
        $json = file_get_contents($view_file);
        $data = @json_decode($json, true);
        if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
            $result[] = "ERROR - GUI - Syntax error in file: $view_file";
            continue;
        }
        if(strpos($view_file, 'form.') > 0) {
            $structure = [
                'name',
                'description',
                'layout' => [
                    'groups' => [
                        'sections' => [
                            'rows' => [
                                'columns' => [
                                    'width',
                                    'items' => [
                                        'type', 'value', 'width'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        elseif(strpos($view_file, 'list.') > 0) {
            $structure = [
                'name',
                'description',
                'layout' => [
                    'items' => [
                        'type', 'value', 'width'
                    ]
                ]
            ];
        }
        elseif(strpos($view_file, 'chart.') > 0) {
            $structure = [
                'name',
                'description',
                'layout' => [
                    'entity',
                    'group_by',
                    'datasets' => [
                        'label', 'operation'
                    ]
                ]
            ];
        }
        else {
            continue;
        }
        // check that mandatory properties are present in the view
        $res = $view_test($data, $structure);
        if($res) {
            $result[] = "ERROR - GUI - ".$res." in file: $view_file";
        }
        // check that fields targeted in views are valid (defined in schema)
        $items = $view_get_items($data, $structure);
        foreach($items as $item) {
            if(isset($item['type']) && $item['type'] == 'field' && isset($item['value'])) {
                $field = $item['value'];
                if(!isset($schema[$field])) {
                    $result[] = "ERROR - GUI - Unknown field '$field' referenced in file $view_file";
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
