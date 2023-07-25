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

/**
 * @var \equal\php\Context      $context
 * @var \equal\route\Router     $router
 */
list($context, $router) = [$providers['context'], $providers['route']];

// get all json routes descriptors sorted by filename in desc order
$files = array_reverse(glob(QN_BASEDIR.'/config/routing/*.json'));

$result = [];

foreach($files as $filepath) {
    $json = file_get_contents($filepath);
    $routes = json_decode($json);
    foreach($routes as $path => $resolver) {
        $result[$path]["methods"] = $resolver;
        $result[$path]["info"]["file"] = basename($filepath);
    }
}

$context->httpResponse()
    ->body($result)
    ->send();
