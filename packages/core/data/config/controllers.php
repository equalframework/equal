<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Returns the list of controllers defined in given package.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'params'        => [
        'package' => [
            'description'   => 'Name of the package for which the list is requested',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'access' => [
        'visibility'    => 'protected'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context          $context
 */
list($context, $orm) = [$providers['context'], $providers['orm']];

$result = [
    'apps'      => recurse_dir("packages/{$params['package']}/apps", 'php', $params['package']),
    'actions'   => recurse_dir("packages/{$params['package']}/actions", 'php', $params['package']),
    'data'      => recurse_dir("packages/{$params['package']}/data", 'php', $params['package'])
];

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
