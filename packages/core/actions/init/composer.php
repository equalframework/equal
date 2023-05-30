<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\http\HttpRequest;
use equal\http\HttpResponse;


list($params, $providers) = announce([
    'description'   => 'Downloads composer and runs it for installing dependencies from composer.json.',
    'params'        => [],
    'access'        => [
        'visibility'    => 'private'
    ],
    'providers'     => ['context'],
]);

list($context) = [$providers['context']];

// stop if composer.json is missing
if(!file_exists('composer.json')) {
    throw new Exception('missing_composer_json', QN_ERROR_MISSING_PARAM);
}

// retrieve the checksum on github
$request = new HttpRequest('GET https://composer.github.io/installer.sig');
/** @var HttpResponse */
$response = $request->send();
$expected_checksum = $response->body();

// download composer-setup script
copy('https://getcomposer.org/installer', 'composer-setup.php');

// if something went wrong during download, stop
if(!file_exists('composer-setup.php')) {
    throw new Exception('missing_file', QN_ERROR_UNKNOWN_OBJECT);
}

// make sure checksum if consistent
if(hash_file('sha384', 'composer-setup.php') !== $expected_checksum) {
    throw new Exception('invalid_checksum', QN_ERROR_UNKNOWN_OBJECT);
}

// run setup and remove script afterward
exec('php composer-setup.php --quiet');
unlink('composer-setup.php');

// check the presence of the executable
if(!file_exists('composer.phar')) {
    throw new Exception('install_failed', QN_ERROR_UNKNOWN);
}

// run composer to install dependencies (quiet mode, no interactions)
exec('php composer.phar install -q -n');

$context->httpResponse()
        ->status(204)
        ->send();