<?php

use equal\orm\ObjectManager;
use equal\php\Context;

list( $params, $providers ) = eQual::announce( [
	'description' => "Generate a configuration file based on a set of params.",
	'response'    => [
		'content-type'  => 'application/json',
		'charset'       => 'UTF-8',
		'accept-origin' => '*'
	],
	'params'      => [
		'dbms'        => [
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
		'db_host'     => [
			'description' => 'The host of the database.',
			'type'        => 'string',
			'required'    => true
		],
		'db_port'     => [
			'description' => 'The tcp port of the DBMS host.',
			'type'        => 'integer',
			'required'    => true
		],
		'db_name'     => [
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
		'app_url'     => [
			'description' => 'The URL of the application.',
			'type'        => 'string',
			'required'    => true
		],
		'store'       => [
			'description' => 'Flag for requesting file creation.',
			'help' => 'Flag for storing the configuration file if true or to only return the content if false',
			'type'        => 'boolean',
			'required'    => true,
		]
	],
	'providers'   => [ 'context', 'orm' ]
] );

/**
 * @var Context $context
 * @var ObjectManager $orm
 */
list( $context, $orm ) = [ $providers['context'], $providers['orm'] ];

$domain_name = getenv('VIRTUAL_HOST');
if (!$domain_name) {
	$domain_name = 'localhost';
}

$https_method = getenv('HTTPS_METHOD');
$protocol = ($https_method == 'noredirect') ? 'http' : 'https';

$backend_url = $protocol.'://'.$domain_name;
$rest_api_url = $protocol.'://'.$domain_name.'/';

// Define the configuration content
$config_content = [
	"DB_DBMS"                    => $params['dbms'],
	"DB_HOST"                    => $params['db_host'],
	"DB_PORT"                    => $params['db_port'],
	"DB_USER"                    => $params['db_username'],
	"DB_PASSWORD"                => $params['db_password'],
	"DB_NAME"                    => $params['db_name'],
	"ROOT_APP_URL"               => $params['app_url'],
	"AUTH_SECRET_KEY"            => bin2hex( random_bytes( 32 ) ),
	"AUTH_ACCESS_TOKEN_VALIDITY" => "1d",
	"USER_ACCOUNT_DISPLAYNAME"   => "nickname",
	"BACKEND_URL" => $backend_url,
	"REST_API_URL" => $rest_api_url,
];

// Convert the configuration content to JSON format
$config_json = json_encode( $config_content, JSON_UNESCAPED_SLASHES );

// Store the file and overwrite if it already exists
if ( $params['store'] === true ) {
	// Define the file path
	$file_path = EQ_BASEDIR . '/config/config.json';

	// Check if the file is writable or if the folder is writable
	if ( ! is_writable( $file_path ) && ! is_writable( dirname( $file_path ) ) ) {
		// Handle error: File or directory is not writable
		throw new Exception( 'non_writable_config', EQ_ERROR_INVALID_CONFIG );
	}

	// Store the configuration file
	file_put_contents( $file_path, $config_json );
}

// Send the response
$context
	->httpResponse()
	->status( 201 )
	->body( $config_content );

