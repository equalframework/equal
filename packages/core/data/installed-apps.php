<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Returns a map with the descriptors of the installed apps (depending on initialized packages).",
    'params'        => [
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context                  $context
 * @var \equal\orm\ObjectManager            $orm
 */
list($context, $orm) = [$providers['context'], $providers['orm']];

// end of process, to have the app slugs and descriptors
$manifestMap = [];
// Reads all the manifest.json inside the public app files
foreach(glob("public/*", GLOB_ONLYDIR) as $app_name){
    $slug = basename($app_name);
    foreach(glob("$app_name/manifest.json") as $manifestJson){
        $manifestJson = json_decode(file_get_contents($manifestJson), true);
        // checks if manifest has an 'apps' section
        if(isset($manifestJson['apps'])){
            foreach($manifestJson['apps'] as $app_name=>$descriptor) {
                $manifestMap[$app_name] = $descriptor;
            }
        }
    }
}

$manifestMap = json_encode($manifestMap);

$context->httpResponse()
        ->body($manifestMap)
        ->send();
