<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list( $params, $providers ) = eQual::announce( [
    'description' => "Generate a configuration file based on a set of params.",
    'response'    => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'      => [
        'domain_name' => [
            'description' => 'The domain name of the installation (virtual host).',
            'type'        => 'string',
            'default'     => getenv('VIRTUAL_HOST')
        ],
        'dbms' => [
            'description' => 'DMBS software brand.',
            'type'        => 'string',
            'selection'   => [
                'MYSQL',
                'SQLSRV',
                'MARIADB',
                'SQLITE',
                'POSTGRESQL'
            ],
            'required'    => true
        ],
        'db_host' => [
            'description' => 'The host of the database.',
            'type'        => 'string',
            'required'    => true
        ],
        'db_port' => [
            'description' => 'The tcp port of the DBMS host.',
            'type'        => 'integer',
            'required'    => true
        ],
        'db_name' => [
            'description' => 'The table name of the database.',
            'type'        => 'string',
            'required'    => true
        ],
        'db_username' => [
            'description' => 'The username of the DBMS host.',
            'type'        => 'string',
            'required'    => true
        ],
        'db_password' => [
            'description' => 'The password of the DBMS host.',
            'type'        => 'string',
            'required'    => true
        ],
        'store'       => [
            'description' => 'Flag for requesting config file creation.',
            'help'        => 'When this flag is set to true, the resulting configuration is stored as `config/config.json`.',
            'type'        => 'boolean',
            'default'     => false
        ]
    ],
    'providers'   => [ 'context' ]
] );

/**
 * @var \equal\php\Context $context
 */
list( $context ) = [ $providers['context'] ];

$domain_name = $params['domain_name'] ?? 'localhost';
$scheme = (getenv('HTTPS_METHOD') == 'noredirect') ? 'http' : 'https';

$config = [
    "DB_DBMS"                    => $params['dbms'],
    "DB_HOST"                    => $params['db_host'],
    "DB_PORT"                    => $params['db_port'],
    "DB_USER"                    => $params['db_username'],
    "DB_PASSWORD"                => $params['db_password'],
    "DB_NAME"                    => $params['db_name'],
    "AUTH_SECRET_KEY"            => bin2hex( random_bytes( 32 ) ),
    "AUTH_ACCESS_TOKEN_VALIDITY" => "1d",
    "USER_ACCOUNT_DISPLAYNAME"   => "nickname",
    "BACKEND_URL"                => $scheme.'://'.$domain_name,
    "REST_API_URL"               => $scheme.'://'.$domain_name.'/',
];

// if store is requested, create the config file (overwrite if it already exists)
if($params['store']) {
    $filepath = EQ_BASEDIR.'/config/config.json';
    // make sure file is writable
    if( !is_writable(dirname($filepath)) || !is_writable($filepath) ) {
        throw new Exception( 'non_writable_config', EQ_ERROR_INVALID_CONFIG );
    }
    // store config
    file_put_contents($filepath, json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    // HTTP 201 Created
    $context->httpResponse()->status(201);
}

$context
    ->httpResponse()
    ->body($config);
