<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Returns a list of existing API (string identifiers).\nResult is based on json files stored into config/routing.\nExpected format is api_{identifier}",
    'deprecated'    => true,
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);


list($context) = [ $providers['context'] ];

$result = [];
foreach(glob(QN_BASEDIR.'/config/routing/api_*.json') as $api_file) {
    $matches = array();
    preg_match ("/api_(.*)\.json/iU", basename($api_file), $matches);
    $result[] = $matches[1];
}

$context->httpResponse()->body($result)->send();