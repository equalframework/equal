<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\Setting;
use core\setting\SettingValue;

list($params, $providers) = announce([
    'description'   => 'Retrieve configuration settings according to package and section filters (support wildcards support) and current user.',
    'params'        => [
        'package' =>  [
            'description'   => "The package which the result list has to be limited to.",
            'type'          => 'string',
            'default'       => '*'
        ],
        'section' =>  [
            'description'   => 'The section which the result list has to be limited to.',
            'type'          => 'string',
			'default' 		=> '*'
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

list($context, $om, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

// retrieve current User identifier (HTTP headers lookup through Authentication Manager)
$user_id = $auth->userId();
// make sure user is authenticated
if($user_id <= 0) {
    throw new Exception('user_unknown', QN_ERROR_NOT_ALLOWED);
}

$domain = [];

if($params['package'] != '*') {
    // get listing of existing packages
    $json = run('get', 'config_packages');
    $data = json_decode($json, true);
    if(isset($data['errors'])) {
        foreach($data['errors'] as $name => $message) {
            throw new Exception($message, qn_error_code($name));
        }
    }
    $packages = $data;
    if(!in_array($params['package'], $packages)) {
        throw new Exception('unknown_package', QN_ERROR_INVALID_PARAM);
    }
    $domain[] = ['package', '=', $params['package']];
}


if($params['section'] != '*') {
    $domain[] = ['section', '=', $params['section']];
}

// fetch related settings values IDS
$collection = Setting::search($domain)
                     ->read(['setting_values_ids'])
                     ->get();

$setting_values_ids = [];

foreach($collection as $oid => $odata) {
    $setting_values_ids = array_merge($setting_values_ids, $odata['setting_values_ids']);
}

$collection = SettingValue::ids($setting_values_ids)
                          ->read(['setting_id' => [ 'id', 'name', 'type', 'package', 'section'], 'value', 'user_id'])
                          ->get();

$result = [];

foreach($collection as $oid => $odata) {
    if(!in_array($odata['user_id'], [0, $user_id])) {
        continue;
    }
    $setting_id = $odata['setting_id']['id'];
    if(!isset($res[$setting_id]) || $odata['user_id'] == $user_id) {
        settype($odata['value'], $odata['setting_id']['type']);
        $res[$setting_id] = [
            'name'      => $odata['setting_id']['name'],
            'package'   => $odata['setting_id']['package'],
            'section'   => $odata['setting_id']['section'],
            'value'     => $odata['value'],
            'user_id'   => $odata['user_id']
        ];
    }
}

// send back basic info of the User object
$context->httpResponse()
        ->body(array_values($res))
        ->send();