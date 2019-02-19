<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
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
            'default'       => '*'
        ]
    ],
    'providers'     => ['context'] 
]);


list($context) = [$providers['context']];



if(!function_exists('get_classes')) {
	function get_classes($package) {
		$data = array();	
		$package_dir = 'packages/'.$package.'/classes';
		if(!is_dir($package_dir) || !($list = scandir($package_dir))) {
			throw new Exception("No classes found for package '{$package}'", QN_ERROR_INVALID_PARAM);        
		}
		foreach($list as $node) {
			if(stristr($node, '.class.php') && is_file($package_dir.'/'.$node)) {
				$data[] = substr($node, 0, -10);
			}
		}
		return $data;
	}
}

$data = array();

// if no package is given, return a map having packages as keys and arrays of related classes as values
if($params['package'] == '*') {
	// get listing of existing packages
	$json = run('get', 'qinoa_config_packages');
	$packages = json_decode($json, true);
	foreach($packages as $package) {
		try {
			$data[$package] = get_classes($package);
		}
		catch(Exception $e) {
			// ignore package with no class definition
			continue;
		}
	}
}
else {
	// if a package is specified, return an array of all related classes
	$data = get_classes($params['package']);
}

    
$context->httpResponse()
        ->body($data)
        ->send();