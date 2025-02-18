<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Provide a map with the descriptors of initialized packages.",
    'help'          => "Info is retrieved from log file `log/packages.json`. This is necessary because status of packages without apps cannot be deduced from `installed-apps`.",
    'deprecated'    => true,
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context      $context
 */
list($context) = [$providers['context']];

// #deprecated - scripts should make direct calls to core_config_live_packages
$result = eQual::run('get', 'core_config_live_packages');

$context->httpResponse()
    ->body($result)
    ->send();
