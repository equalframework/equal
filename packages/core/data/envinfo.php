<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use core\setting\SettingValue;

list($params, $providers) = eQual::announce([
    'description'   => 'Returns a descriptor of current installation Settings, holding specific values for current User, if applicable.',
    'access'        => [
        'visibility'        => 'protected'
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context', 'orm', 'auth']
]);

list($context, $om, $auth) = [$providers['context'], $providers['orm'], $providers['auth']];

// retrieve current User identifier (through HTTP headers lookup by Authentication Manager)
$user_id = $auth->userId();

$result = [];

// 1) read global settings
$settings = SettingValue::search(['user_id', '=', 0])->read(['name', 'value'])->get();

foreach($settings as $sid => $setting) {
    $result[$setting['name']] = $setting['value'];
}

// 2) overload with current User specific settings, if any
$settings = SettingValue::search(['user_id', '=', $user_id])->read(['name', 'value'])->get();

foreach($settings as $sid => $setting) {
    $result[$setting['name']] = $setting['value'];
}

// send back basic info of the User object
$context->httpResponse()
        ->body($result)
        ->send();
