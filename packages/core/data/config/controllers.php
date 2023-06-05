<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Returns the list of controllers defined in given package',
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
    'providers'     => ['context']
]);


function glob_recursive($directory) {
    $files = glob($directory.'/*.php');
    foreach (glob($directory.'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, glob_recursive($dir));
    }
    return $files;
}

function recurse_dir($directory, $parent_name='') {
    $result = array();
    if( is_dir($directory) ) {
        $list = glob($directory.'/*');
        foreach($list as $node) {
            $scriptname = basename($node, '.php');
            if(is_dir($node)) {
                if(count(glob_recursive($node)) === 0) continue;
                $result = array_merge($result, recurse_dir($node, (strlen($parent_name)?$parent_name.'_'.$scriptname:$scriptname)));
            }
            elseif(pathinfo($node, PATHINFO_EXTENSION) == 'php'){
                $result[] = (strlen($parent_name)?$parent_name.'_':'').$scriptname;
            }
        }
    }
    return $result;
}

$php_scripts = function ($directory) use ($params){
	$result = array();
    $result = recurse_dir("packages/{$params['package']}/$directory");
	return $result;
};


$result = [
    // 'apps'      => $php_scripts('apps'),
    'actions'   => $php_scripts('actions'),
    'data'      => $php_scripts('data')
];


$context->httpResponse()
        ->body($result)
        ->send();
