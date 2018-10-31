<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;

list($params, $providers) = QNLib::announce([
    'description'   => 'Returns existing routes for a given API, along with their description and expected parameters',
    'params'        => [
                        'api'   => [
                            'description'   => 'The name (string identifier) of the API for which routes are requested',
                            'type'          => 'string',
                            'required'      => true            
                       ]
    ],
    'providers'     => ['context', 'route'] 
]);


list($context, $router) = [$providers['context'], $providers['route']];

$routes = $router->add(QN_BASE_DIR."/config/routing/api_{$params['api']}.json")->getRoutes();

$result = [];

foreach($routes as $path => $resolver) {
    $batch = $router->normalize($path, $resolver);
    foreach($batch as $route) {
        if(isset($route['redirect']) || $route['operation']['type'] == 'show') continue;

        // add 'announce' parameter to force script to announce itself (script description)
        $route['operation']['params']['announce'] = true;

        $json = QNLib::run($route['operation']['type'], $route['operation']['name'], $route['operation']['params']);
        $announce = json_decode($json, true);

        $descriptor = [
            'uri'           => $path,
            'method'        => $route['method']
        ];

        if(isset($announce['announcement']['description'])) {
            $descriptor['description'] = $announce['announcement']['description'];
        }

        if(isset($announce['announcement']['params'])) {
            $descriptor['params'] = $announce['announcement']['params'];
        }

        if(isset($announce['announcement']['response'])) {
            $descriptor['response'] = $announce['announcement']['response'];
        }
        
        $result[] = $descriptor;
    }
    
}

$context->httpResponse()->body($result)->send();