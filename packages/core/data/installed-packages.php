<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
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
