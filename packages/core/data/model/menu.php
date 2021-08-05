<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Returns the JSON menu related to a package, given a menu ID (<name>).",
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package the menu relates to (e.g. \'core\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'menu_id' =>  [
            'description'   => 'The identifier of the menu <name>.',
            'type'          => 'string', 
            'default'       => 'list'
        ],
        
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'        
    ],
    'providers'     => ['context', 'orm'] 
]);


list($context, $orm) = [$providers['context'], $providers['orm']];

// retrieve existing view meant for package
$file = QN_BASEDIR."/packages/{$params['package']}/views/menu.{$params['menu_id']}.json";

if(!file_exists($file)) {
    throw new Exception("unknown_view_id", QN_ERROR_UNKNOWN_OBJECT);
}

if( ($view = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_view_schema", QN_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->body($view)
        ->send();