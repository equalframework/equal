<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;

list($params, $providers) = QNLib::announce([
    'description'   => 'Returns a list of existing API (string identifiers)',
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8'        
    ],
    'providers'     => ['context', 'route'] 
]);


list($context, $router) = [$providers['context'], $providers['route']];

$result = [];
foreach(glob(QN_BASE_DIR.'/config/routing/api_*.json') as $api_file) {
    $matches = array();
    preg_match ("/api_(.*)\.json/iU", basename($api_file), $matches);
    $result[] = $matches[1];
}

$context->httpResponse()->body($result)->send();