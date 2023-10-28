<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => 'Verify major checkpoints to ensure current installation is healthy.',
    'params'        => [
    ],
    'constants'     => ['ENV_MODE', 'AUTH_SECRET_KEY', 'AUTH_ACCESS_TOKEN_VALIDITY', 'AUTH_TOKEN_HTTPS', 'DEFAULT_RIGHTS', 'DEFAULT_LANG', 'DEBUG_LEVEL'],
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context                $context
 * @var \equal\error\Reporter             $reporter
 */
list($context) = [$providers['context']];

$result = [];

if(!defined('ENV_MODE')) {
    $result[] = 'WARN  - CFG - [ENV_MODE] It is recommended to explicitly define the mode under which the installation operates.';
}

if(constant('ENV_MODE') == 'production') {
    if(file_exists(QN_BASEDIR.'/public/console.php')) {
        $result[] = 'WARN  - SEC - [ENV_MODE] Allowing logs access in production is a potential security breach.';
    }
    if(constant('AUTH_SECRET_KEY') == 'my_secret_key') {
        $result[] = 'WARN  - SEC - [AUTH_SECRET_KEY] Using default secret key in production is a potential security breach.';
    }
    if(constant('AUTH_ACCESS_TOKEN_VALIDITY') > 86400) {
        $result[] = 'WARN  - SEC - [AUTH_ACCESS_TOKEN_VALIDITY] Using hight lifespan for access token in production is a potential security breach.';
    }
    if(constant('DEFAULT_RIGHTS') & R_WRITE == R_WRITE) {
        $result[] = 'WARN  - SEC - [DEFAULT_RIGHTS] WRITE permission to all users in production is a potential security breach.';
    }
    if(constant('AUTH_TOKEN_HTTPS') == false) {
        $result[] = 'WARN  - SEC - [AUTH_TOKEN_HTTPS] It is recommended to exchange auth token over HTTPS only.';
    }
    if(constant('DEBUG_LEVEL') >= QN_REPORT_INFO) {
        $result[] = 'WARN  - CFG - [DEBUG_LEVEL] Using a high debug level will generate a large amount of logs and can result in lower performances.';
    }
}

if(constant('DEFAULT_LANG') != 'en') {
    $result[] = 'WARN  - CFG - [DEFAULT_LANG] It is recommended to use \'en\' as default language.';
}

$context->httpResponse()
        ->status(200)
        ->body($result)
        ->send();
