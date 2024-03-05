<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Create a database according to the configuration',
    'params'        => [],
    'providers'     => ['context', 'orm'],
    'constants'     => ['ENV_MODE', 'ROOT_APP_URL', 'APP_NAME', 'APP_LOGO_URL', 'DEFAULT_LANG', 'L10N_LOCALE', 'EQ_VERSION', 'ORG_NAME', 'ORG_URL'],
]);

list($context, $orm) = [$providers['context'], $providers['orm']];

// init database
eQual::run('do', 'init_db');

// export config to public folder
$config = [
    'production'    => constant('ENV_MODE'),
    'parent_domain' => parse_url(constant('ROOT_APP_URL'), PHP_URL_HOST),
    'backend_url'   => constant('ROOT_APP_URL'),
    'rest_api_url'  => constant('ROOT_APP_URL').'/',
    'lang'          => constant('DEFAULT_LANG'),
    'locale'        => constant('L10N_LOCALE'),
    'version'       => constant('EQ_VERSION'),
    'company_name'  => constant('ORG_NAME'),
    'company_url'   => constant('ORG_URL'),
    'app_name'      => constant('APP_NAME'),
    'app_logo_url'  => constant('APP_LOGO_URL')
];

$json = json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
file_put_contents(QN_BASEDIR.'/public/assets/env/config.json', $json);


// create mandatory files
