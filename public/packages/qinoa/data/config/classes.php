<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;

list($params, $providers) = QNLib::announce([
    'description'   => 'Returns the list of classes defined in specified package',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
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


list($context) = [$providers['context']];


$data = array();

$package_dir = 'packages/'.$params['package'].'/classes';
if(!is_dir($package_dir) || !($list = scandir($package_dir))) {
    throw new Exception("'{$params['package']}' not found", QN_ERROR_INVALID_CONFIG);        
}
foreach($list as $node) {
    if(stristr($node, '.class.php') && is_file($package_dir.'/'.$node)) {
        $data[] = substr($node, 0, -10);
    }
}
    
$context->httpResponse()
        ->body($data)
        ->send();