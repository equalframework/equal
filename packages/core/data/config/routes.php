<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Returns all the routes with priority based on the number.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'params'        => [],
    'providers'     => ['context', 'route']
]);


list($context, $router) = [$providers['context'], $providers['route']];

$routes = scandir(QN_BASEDIR."/config/routing");

$routes = array_filter($routes, function($value) {
    return preg_match('/^\d/', $value);
});


usort($routes, function ($a, $b) {
    $x = intval(preg_replace('/\D/', '', $a));
    $y = intval(preg_replace('/\D/', '', $b));
    return $y - $x;
});

$result = [];

foreach($routes as $key => $value) {
    $json_string = file_get_contents(QN_BASEDIR."/config/routing/".$value);
    $php_array = json_decode($json_string);
    foreach($php_array as $path => $resolver) {
        $result[$path]["methods"] = $resolver;
        $result[$path]["info"]["file"] = $value;
    }
}


$context->httpResponse()
    ->body($result)
    ->send();
