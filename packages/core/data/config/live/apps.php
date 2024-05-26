<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Returns a map with the descriptors of the installed apps (depending on initialized packages).",
    'access'        => [
        'visibility'    => 'protected'
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

$map_manifests = [];

// search and read manifest.json inside App folders within /public
foreach(glob("public/*", GLOB_ONLYDIR) as $app_path) {
    if(file_exists("$app_path/manifest.json")) {
        if($manifest = json_decode(file_get_contents("$app_path/manifest.json"), true)) {
            $map_manifests[basename($app_path)] = $manifest;
        }
    }
}

// loop through initialized packages to include apps extending others
if(file_exists("log/packages.json")) {
    $packages = json_decode(file_get_contents("log/packages.json"), true);
    foreach($packages as $package => $datetime) {
        if(file_exists("packages/$package/manifest.json")) {
            $manifest = json_decode(file_get_contents("packages/$package/manifest.json"), true);
            if(!isset($manifest['apps'])) {
                continue;
            }
            // handle apps using app descriptors
            foreach($manifest['apps'] as $app) {
                // discard string notation (refers to an app ID)
                if(!is_array($app)) {
                    continue;
                }
                // discard apps not extending another App
                if(!isset($app['extends'])) {
                    continue;
                }
                // discard apps with missing if
                if(!isset($app['id'])) {
                    continue;
                }
                // discard apps extending a non-installed App
                if(!file_exists("public/{$app['extends']}") || !is_dir("public/{$app['extends']}")) {
                    continue;
                }
                $app['url'] = "/{$app['extends']}/#/$package/{$app['id']}";
                // force app to be visible
                $app['show_in_apps'] = true;
                $map_manifests[$app['id']] = $app;
            }
        }
    }
}

$context->httpResponse()
    ->body($map_manifests)
    ->send();
