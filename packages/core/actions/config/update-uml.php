<?php

list($params, $providers) = eQual::announce([
    'description'   => "Attempts to create a new package using a given name.",
    'params'        => [
        'package'   => [
            'description'   => 'Name of the package of the new model',
            'type'          => 'string',
            'required'      => true
        ],
        'filename' =>  [
            'description'   => 'relative path to the file from packages/{package}/uml',
            'type'          => 'string',
            'required'      => true
        ],
        'payload'   => [
            'description'   => 'value of the field',
            'type'          => 'text',
            'required'      => true
        ],
        'type'      => [
            'description'   => 'Type of the UML data',
            'type'          => 'string',
            'required'      => true,
            'selection'     => [
                'erd'
            ]
        ]
    ],
    'response'      => [
        'content-type'  => 'text',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context', 'orm', 'access']
]);

/**
 * @var \equal\php\Context              $context
 * @var \equal\orm\ObjectManager        $orm
 * @var \equal\access\AccessController  $ac
 */
list($context, $orm, $ac) = [$providers['context'], $providers['orm'], $providers['access']];

$response_code = 200;

$package = $params['package'];

if(!file_exists(QN_BASEDIR."/packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_PARAM);
}

if(!file_exists(QN_BASEDIR."/packages/{$package}/uml")) {
    if(!mkdir(QN_BASEDIR."/packages/{$package}/uml", 0775, true)) {
        throw new Exception('io_error'.QN_BASEDIR."/packages/{$package}/uml/{$path}", QN_ERROR_INVALID_CONFIG);
    }
}

$filepath = str_replace(['..', '/'], ['', '_'], $params['filename']);

$parts = explode("_", $filepath);
$filename = array_pop($parts);

$path = implode('/', $parts);

$extension = ".{$params["type"]}.json";

if(substr($filename, -strlen($extension)) != $extension) {
    $filename = $filename.$extension;
}

$final_path = QN_BASEDIR."/packages/{$package}/uml/" . ( (strlen($path))?($path.'/'):'' );

if(!is_dir($final_path)) {
    if(!mkdir($final_path, 0775, true)) {
        throw new Exception('io_error', QN_ERROR_INVALID_CONFIG);
    }
}

if(!file_exists($final_path.$filename)) {
    $response_code = 201;
}

// store payload
$f = fopen($final_path.$filename, "w");
if(!$f) {
    throw new Exception('io_error', QN_ERROR_INVALID_CONFIG);
}
fputs($f, $params['payload']);
fclose($f);

$result = file_get_contents($final_path.$filename);

$context->httpResponse()
        ->body($result)
        ->status($response_code)
        ->send();
