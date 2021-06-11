<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Returns the list of public packages in current installation',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context'] 
]);


list($context) = [$providers['context']];


$data = array();
$packages_dir = 'packages';
if(!is_dir($packages_dir) || !($list = scandir($packages_dir))) {
    throw new Exception('packages directory not found', QN_ERROR_INVALID_CONFIG);
}
    
foreach($list as $node) {
    if(is_dir($packages_dir.'/'.$node) && !in_array($node, array('.', '..')) && $node[0] != '.') {
        $data[] = $node;
    }
}    
    
$context->httpResponse()
        ->body($data)
        ->send();