<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Returns the JSON view related to an entity (class model), given a view ID (<type.name>).",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'view_id' =>  [
            'description'   => 'The identifier of the view <type.name>.',
            'type'          => 'string',
            'default'       => 'list.default'
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm']
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

$removeNodes = function (&$layout, $nodes_ids) {
    foreach($layout['groups'] ?? [] as $group_index => $group) {
        if(isset($group['id']) && in_array($group['id'], $nodes_ids)) {
            array_splice($layout['groups'], $group_index, 1);
            continue;
        }
        foreach($group['sections'] ?? [] as $section_index => $section) {
            if(isset($section['id']) && in_array($section['id'], $nodes_ids)) {
                array_splice($layout['groups'][$group_index]['sections'], $section_index, 1);
                continue;
            }
            foreach($section['rows'] ?? [] as $row_index => $row) {
                if(isset($row['id']) && in_array($row['id'], $nodes_ids)) {
                    array_splice($layout['groups'][$group_index]['sections'][$section_index]['rows'], $row_index, 1);
                    continue;
                }
                foreach($row['columns'] ?? [] as $column_index => $column) {
                    if(isset($column['id']) && in_array($column['id'], $nodes_ids)) {
                        array_splice($layout['groups'][$group_index]['sections'][$section_index]['rows'][$row_index]['columns'], $column_index, 1);
                        continue;
                    }
                    foreach($column['items'] ?? [] as $item_index => $item) {
                        if(isset($item['id']) && in_array($item['id'], $nodes_ids)) {
                            array_splice($layout['groups'][$group_index]['sections'][$section_index]['rows'][$row_index]['columns'][$column_index]['items'], $item_index, 1);
                            continue;
                        }
                    }
                }
            }
        }
    }

    foreach($layout['items'] ?? [] as $item_index => $item) {
        if(isset($item['id']) && in_array($item['id'], $nodes_ids)) {
            array_splice($layout['items'], $item_index, 1);
        }
    }
};

$updateNode = function (&$layout, $id, $node) {
    $target = null;
    $target_type = '';
    $index = 0;
    $target_parent = null;
    foreach($layout['groups'] as $group_index => $group) {
        if(isset($group['id']) && $group['id'] == $id) {
            $target = &$layout['groups'][$group_index];
            break;
        }
        $target_parent = &$layout['groups'][$group_index]['sections'];
        foreach($group['sections'] as $section_index => $section) {
            if(isset($section['id']) && $section['id'] == $id) {
                $target = &$layout['groups'][$group_index]['sections'][$section_index];
                $target_type = 'section';
                $index = $section_index;
                break 2;
            }
            $target_parent = &$layout['groups'][$group_index]['sections'][$section_index]['rows'];
            foreach($section['rows'] as $row_index => $row) {
                if(isset($row['id']) && $row['id'] == $id) {
                    $target = &$layout['groups'][$group_index]['sections'][$section_index]['rows'][$row_index];
                    $target_type = 'row';
                    $index = $row_index;
                    break 3;
                }
                $target_parent = &$layout['groups'][$group_index]['sections'][$section_index]['rows'][$row_index]['columns'];
                foreach($row['columns'] as $column_index => $column) {
                    if(isset($column['id']) && $column['id'] == $id) {
                        $target = &$layout['groups'][$group_index]['sections'][$section_index]['rows'][$row_index]['columns'][$column_index];
                        $target_type = 'column';
                        $index = $column_index;
                        break 4;
                    }
                    $target_parent = &$layout['groups'][$group_index]['sections'][$section_index]['rows'][$row_index]['columns'][$column_index]['items'];
                    foreach($column['items'] as $item_index => $item) {
                        if(isset($item['id']) && $item['id'] == $id) {
                            $target = &$layout['groups'][$group_index]['sections'][$section_index]['rows'][$row_index]['columns'][$column_index]['items'][$item_index];
                            $target_type = 'item';
                            $index = $item_index;
                            break 5;
                        }
                    }
                }
            }
        }
    }

    if(isset($layout['items'])) {
        $target_parent = &$layout['items'];
        foreach($layout['items'] as $item_index => $item) {
            if(isset($item['id']) && $item['id'] == $id) {
                $target = &$layout['items'][$item_index];
                $index = $item_index;
            }
        }
    }

    if($target) {
        if(isset($node['attributes'])) {
            foreach((array) $node['attributes'] as $attribute => $value) {
                $target[$attribute] = $value;
            }
        }
        if(isset($node['prepend'])) {
            foreach((array) $node['prepend'] as $elem) {
                if($target_type == 'column') {
                    array_unshift($target['items'], $elem);
                }
                else {
                    array_unshift($target, $elem);
                }
            }
        }
        if(isset($node['append'])) {
            foreach((array) $node['append'] as $elem) {
                if($target_type == 'column') {
                    array_push($target['items'], $elem);
                }
                else {
                    array_push($target, $elem);
                }
            }
        }
        if($target_parent) {
            if(isset($node['before'])) {
                array_splice($target_parent, $index, 0, (array) $node['before']);
                $index += count((array) $node['before']);
            }
            if(isset($node['after'])) {
                array_splice($target_parent, $index + 1, 0, (array) $node['after']);
            }
        }
    }
};


$entity = $params['entity'];

list($view_type, $view_name) = explode('.', $params['view_id']);

if(!in_array($view_type, ['form', 'list', 'chart', 'search', 'report', 'cards', 'dashboard'])) {
    throw new Exception('invalid_view_type', EQ_ERROR_INVALID_PARAM);
}

// pass-1 : retrieve existing view meant for entity (recurse through parents)
while(true) {
    $parts = explode('\\', $entity);
    $package = array_shift($parts);
    $filename = array_pop($parts);
    $class_path = implode('/', $parts);

    $file = QN_BASEDIR."/packages/{$package}/views/{$class_path}/{$filename}.{$view_type}.{$view_name}.json";
    if(file_exists($file)) {
        break;
    }

    // fallback to default variant of the view
    $file = QN_BASEDIR."/packages/{$package}/views/{$class_path}/{$filename}.{$view_type}.default.json";
    if(file_exists($file)) {
        break;
    }

    // go one level up through parents
    try {
        $parent = get_parent_class($orm->getModel($entity));
        if(!$parent || $parent == 'equal\orm\Model') {
            break;
        }
        $entity = $parent;
    }
    catch(Exception $e) {
        // support for controller entities
        break;
    }
}

if(!file_exists($file)) {
    throw new Exception("missing_view", QN_ERROR_UNKNOWN_OBJECT);
}

if(($view = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
}

if(!isset($view['layout'])) {
    throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
}

// pass-2 : adapt the view if inheritance is involved
if(isset($view['layout']['extends'])) {
    if(!isset($view['layout']['extends']['view'])) {
        throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
    }
    $view_id = $view['layout']['extends']['view'];
    $entity = $view['layout']['extends']['entity'] ?? $params['entity'];
    if($params['view_id'] == $view_id && $params['entity'] == $entity) {
        throw new Exception("cyclic_view_dependency", QN_ERROR_INVALID_CONFIG);
    }
    $parent_view = eQual::run('get', 'model_view', ['entity' => $entity, 'view_id' => $view_id]);
    if(isset($view['layout']['remove'])) {
        $removeNodes($parent_view['layout'], (array) $view['layout']['remove']);
    }
    if(isset($view['layout']['update'])) {
        foreach((array) $view['layout']['update'] as $id => $node) {
            $updateNode($parent_view['layout'], $id, $node);
        }
    }
    $view['layout'] = $parent_view['layout'];
}

$context->httpResponse()
        ->body($view)
        ->send();
