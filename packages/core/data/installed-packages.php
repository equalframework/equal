<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Provide a map with the descriptors of initialized packages.",
    'help'          => "Info is retrieved from log file `log/packages.json`. This is necessary because status of packages without apps cannot be deduced from `installed-apps`.",
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

$map_packages = [];

if(file_exists("public/app") && file_exists("log/packages.json")) {
    $map_packages = json_decode(file_get_contents("log/packages.json"), true);
}

$context->httpResponse()
    ->body(array_keys($map_packages))
    ->send();
