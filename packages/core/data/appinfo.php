<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Retrieve the descriptor of a given App (from manifest), identified by package and app ID.',
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package the app is part of.',
            'required'      => true
        ],
        'app' => [
            'type'          => 'string',
            'description'   => 'Identifier of the app for which the descriptor is requested.',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'UTF-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
list($context) = [$providers['context']];

list($package, $app) = [$params['package'], $params['app']];

$result = [];

// look within the given package manifest for a descriptor matching requested app
if(file_exists("packages/$package/manifest.json")) {
    $manifest = json_decode(file_get_contents("packages/$package/manifest.json"), true);
    if(isset($manifest['apps'])) {
        // handle apps using app descriptors
        foreach($manifest['apps'] as $descriptor) {
            if(!is_array($descriptor)) {
                // descriptor is a string (app name)
                if($app != $descriptor) {
                    continue;
                }
                // lookup in public/{app}/manifest
                if(file_exists("public/$app/manifest.json")) {
                    $result = json_decode(file_get_contents("public/$app/manifest.json"), true);
                }
            }
            elseif(isset($descriptor['id']) && $descriptor['id'] == $app) {
                $result = $descriptor;
            }
            break;
        }
    }
}

// send back basic info of the User object
$context->httpResponse()
        ->body($result)
        ->send();
