<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Returns the list of views defined in a given package, or applicable to a given entity.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'params'        => [
        'package' => [
            'description'   => 'Name of the package for which the list is requested.',
            'type'          => 'string'
        ],
        'entity' => [
            'description'   => 'Name of the entity for which the list is requested.',
            'type'          => 'string'
        ]
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
list($context, $orm) = [$providers['context'], $providers['orm']];

$result = [];

if(isset($params['package'])) {
    if(!file_exists("packages/{$params['package']}")) {
        throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
    }
    if(!file_exists("packages/{$params['package']}/views")) {
        throw new Exception('missing_views_dir', QN_ERROR_INVALID_CONFIG);
    }
    // recurse through all sub-folders of `views` directory
    $result = recurse_dir("packages/{$params['package']}/views", 'json', $params['package']);
}
else {
    if(!isset($params['entity'])) {
        throw new Exception('package_or_entity_required', QN_ERROR_MISSING_PARAM);
    }
    // #todo : add support for controller entities
    if(!class_exists($params['entity'])) {
        throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
    }
    $entity = $params['entity'];
    $map_views_ids = [];
    // retrieve all views meant either directly for package entities or any of their parents
    while(true) {
        $parts = explode('\\', $entity);
        $package = array_shift($parts);
        $file = array_pop($parts);
        $class_path = implode('/', $parts);

        $list = glob(QN_BASEDIR."/packages/{$package}/views/{$class_path}/{$file}.*.json");

        foreach($list as $node) {
            $view_file = basename($node, '.json');
            list($entity_name, $view_type, $view_name, $extension) = explode('.', $view_file);
            $view_id = $view_type.'.'.$view_name;
            if(!isset($map_views_ids[$view_id])) {
                $map_views_ids[$view_id] = true;
                $result[] = $package.'\\'.(strlen($class_path)?str_replace('/', '\\', $class_path).'\\':'').$entity_name.':'.$view_id;
            }
        }
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

}

$context->httpResponse()
        ->body($result)
        ->send();

function has_sub_items($directory, $extension) {
    $files = glob($directory.'/*.'.$extension);
    if(count($files)) {
        return true;
    }
    foreach(glob($directory.'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $node) {
        if(has_sub_items($node, $extension)) {
            return true;
        }
    }
    return false;
}

/**
 * #memo - this method slightly differs from the one in controllers.php and translations.php
 */
function recurse_dir($directory, $extension, $parent_name='') {
    $result = array();
    if( is_dir($directory) ) {
        $dir_name = basename($directory);
        $list = glob($directory.'/*');
        foreach($list as $node) {
            $filename = basename($node, '.'.$extension);
            list($entity_name, $view_id) = explode('.', $filename, 2);
            if(is_dir($node)) {
                if(!has_sub_items($node, $extension)) {
                    continue;
                }
                $result = array_merge($result, recurse_dir($node, $extension, (strlen($parent_name)?$parent_name.'\\'.$filename:$filename)));
            }
            elseif(pathinfo($node, PATHINFO_EXTENSION) == $extension) {
                $entity = (strlen($parent_name)?$parent_name.'\\':'').$entity_name;
                try {
                    $entity::getType();
                    $result[] = $entity.':'.$view_id;
                }
                catch(Exception $e) {
                    if($entity_name == 'menu') {
                        // ignore
                    }
                    else {
                        // entity should be a controller
                        // #todo - check that the controller actually exists
                        $result[] = str_replace('\\', '_', $entity).':'.$view_id;
                    }
                }
            }
        }
    }
    return $result;
}
