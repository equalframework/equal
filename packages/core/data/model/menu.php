<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
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
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
list($context) = [ $providers['context'] ];

$result = [];

// retrieve existing view meant for package
$file = EQ_BASEDIR . "/packages/{$params['package']}/views/menu.{$params['menu_id']}.json";

// #memo - to prevent untimely log entries, this script always return a non-404 error
if(file_exists($file) && ($view = json_decode(@file_get_contents($file), true)) !== null) {
    $result = $view;
}
elseif(strpos($params['menu_id'], '.') !== false) {
    $parts = explode('.', $params['menu_id']);
    array_pop($parts);
    $menu_id = implode('.', $parts);
    $file = EQ_BASEDIR . "/packages/{$params['package']}/views/menu.{$menu_id}.json";
    if(file_exists($file) && ($view = json_decode(@file_get_contents($file), true)) !== null) {
        $result = $view;
    }
}

$context->httpResponse()
        ->body($result)
        ->send();
