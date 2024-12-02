<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Generate a configuration file based on a set of params and store it as `config/config.json`.",
    'params'        => [
        'domain_name' => [
            'description'   => "The domain name of the installation (virtual host).",
            'type'          => 'string',
            'default'       => getenv('VIRTUAL_HOST')
        ],
        'scheme' => [
            'description'   => "The scheme of the installation (https method).",
            'type'          => 'string',
            'selection'     => [
                'http',
                'https'
            ],
            'default'       => (getenv('HTTPS_METHOD') === 'noredirect') ? 'http' : 'https'
        ],
        'dbms' => [
            'description'   => "DBMS software brand.",
            'type'          => 'string',
            'selection'     => [
                'MYSQL',
                'SQLSRV',
                'MARIADB',
                'SQLITE',
                'POSTGRESQL'
            ],
            'required'      => true
        ],
        'db_host' => [
            'description'   => "The host of the database.",
            'type'          => 'string',
            'required'      => true
        ],
        'db_port' => [
            'description'   => "The tcp port of the DBMS host.",
            'type'          => 'integer',
            'required'      => true
        ],
        'db_name' => [
            'description'   => "The table name of the database.",
            'type'          => 'string',
            'required'      => true
        ],
        'db_username' => [
            'description'   => "The username of the DBMS host.",
            'type'          => 'string',
            'required'      => true
        ],
        'db_password' => [
            'description'   => "The password of the DBMS host.",
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$config = [
    'DB_DBMS'                       => $params['dbms'],
    'DB_HOST'                       => $params['db_host'],
    'DB_PORT'                       => $params['db_port'],
    'DB_USER'                       => $params['db_username'],
    'DB_PASSWORD'                   => $params['db_password'],
    'DB_NAME'                       => $params['db_name'],
    'AUTH_SECRET_KEY'               => bin2hex(random_bytes(32)),
    'AUTH_ACCESS_TOKEN_VALIDITY'    => "1d",
    'USER_ACCOUNT_DISPLAYNAME'      => "nickname",
    'BACKEND_URL'                   => $params['scheme'].'://'.$params['domain_name'],
    'REST_API_URL'                  => $params['scheme'].'://'.$params['domain_name'].'/'
];

$filepath = EQ_BASEDIR.'/config/config.json';

if(!is_writable(dirname($filepath))) {
    throw new Exception("non_writable_config", EQ_ERROR_INVALID_CONFIG);
}

if(file_exists($filepath)) {
    throw new Exception("config_already_exists", EQ_ERROR_NOT_ALLOWED);
}

file_put_contents(
    $filepath,
    json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
);

$context->httpResponse()
        ->status(201)
        ->send();
