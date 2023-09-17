<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Returns the list of translation files defined in a given package, or applicable to a given entity.',
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
    if(!file_exists("packages/{$params['package']}/i18n")) {
        throw new Exception('missing_views_dir', QN_ERROR_INVALID_CONFIG);
    }
    // recurse through all sub-folders of `i18n` directory
    // get the list of languages directories
    foreach(glob("packages/{$params['package']}/i18n/*", GLOB_ONLYDIR|GLOB_NOSORT) as $node) {
        $lang = basename($node);
        if(!isset($result[$lang])) {
            $result[$lang] = [];
        }
        $result[$lang] = recurse_dir("packages/{$params['package']}/i18n/{$lang}", 'json', $params['package']);
    }
}
else {
    if(!isset($params['entity'])) {
        throw new Exception('package_or_entity_required', QN_ERROR_MISSING_PARAM);
    }
    if(!class_exists($params['entity'])) {
        throw new Exception('unknown_entity', QN_ERROR_INVALID_PARAM);
    }
    $entity = $params['entity'];
    $map_views_ids = [];
    // retrieve all translations meant either directly for package entities or any of their parents
    while(true) {
        $parts = explode('\\', $entity);
        $package = array_shift($parts);
        $file = array_pop($parts);
        $class_path = implode('/', $parts);

        foreach(glob("packages/{$package}/i18n/*", GLOB_ONLYDIR|GLOB_NOSORT) as $lang_node) {
            $lang = basename($lang_node);
            if(!isset($result[$lang])) {
                $result[$lang] = [];
            }

            $list = glob(QN_BASEDIR."/packages/{$package}/i18n/{$lang}/{$class_path}/{$file}.json");

            foreach($list as $node) {
                $result[$lang][] = $package.'_'.(strlen($class_path)?str_replace('/', '_', $class_path).'_':'').basename($node, '.json');
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

function recurse_dir($directory, $extension, $parent_name='') {
    $result = array();
    if( is_dir($directory) ) {
        $dir_name = basename($directory);
        $list = glob($directory.'/*');
        foreach($list as $node) {
            $script_name = basename($node, '.'.$extension);
            if(is_dir($node)) {
                if($dir_name == 'apps') {
                    // do not process subdirectories for apps
                    continue;
                }
                if(!has_sub_items($node, $extension)) {
                    continue;
                }
                $result = array_merge($result, recurse_dir($node, $extension, (strlen($parent_name)?$parent_name.'_'.$script_name:$script_name)));
            }
            elseif(pathinfo($node, PATHINFO_EXTENSION) == $extension){
                $result[] = (strlen($parent_name)?$parent_name.'_':'').$script_name;
            }
        }
    }
    return $result;
}
