<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2024
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use equal\http\HttpRequest;
use equal\http\HttpResponse;


list($params, $providers) = eQual::announce([
    'description'   => "Downloads composer and runs it for installing dependencies from composer.json.",
    'help'          => "This controller rely on the PHP binary. In order to make them work, sure the PHP binary is present in the PATH.",
    'params'        => [],
    'access'        => [
        'visibility'    => 'private'
    ],
    'providers'     => ['context'],
]);

list($context) = [$providers['context']];

// stop if composer.json is missing
if(!file_exists(EQ_BASEDIR.'/composer.json')) {
    throw new Exception('missing_composer_json', EQ_ERROR_MISSING_PARAM);
}

if(!file_exists(EQ_BASEDIR.'/composer.phar')) {
    // retrieve the checksum on github
    $request = new HttpRequest('GET https://composer.github.io/installer.sig');
    /** @var HttpResponse */
    $response = $request->send();
    $expected_checksum = $response->body();

    // download composer-setup script
    copy('https://getcomposer.org/installer', EQ_BASEDIR.'/composer-setup.php');

    // if something went wrong during download, stop
    if(!file_exists(EQ_BASEDIR.'/composer-setup.php')) {
        throw new Exception('missing_file', EQ_ERROR_UNKNOWN_OBJECT);
    }

    // make sure checksum if consistent
    if(hash_file('sha384', EQ_BASEDIR.'/composer-setup.php') !== $expected_checksum) {
        throw new Exception('invalid_checksum', EQ_ERROR_UNKNOWN_OBJECT);
    }

    // run setup and remove script afterward
    if(exec('php composer-setup.php --quiet') === false) {
        throw new Exception('command_failed', EQ_ERROR_UNKNOWN);
    }
    unlink(EQ_BASEDIR.'/composer-setup.php');

    // check the presence of the executable
    if(!file_exists(EQ_BASEDIR.'/composer.phar')) {
        throw new Exception('install_failed', EQ_ERROR_UNKNOWN);
    }
}

if(file_exists(EQ_BASEDIR.'/composer.lock')) {
    unlink(EQ_BASEDIR.'/composer.lock');
}

// run composer to install dependencies (quiet mode, no interactions, ignore PHP version)
if(exec('php composer.phar install --ignore-platform-reqs -q -n') === false) {
    throw new Exception('composer_failed', EQ_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(204)
        ->send();
