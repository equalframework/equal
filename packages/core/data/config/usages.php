<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => 'Returns an associative array mapping orm types with possible usages and their descriptors.',
    'response'      => [
        'content-type'      => 'application/json',
        'charset'           => 'utf-8',
        'accept-origin'     => '*'
    ],
    'params'        => [],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context          $context
 */
list($context) = [$providers['context']];

if(!file_exists(QN_BASEDIR."/config/usages.json")) {
    throw new Exception("missing_usages_file", QN_ERROR_UNKNOWN);
}

$content = file_get_contents(QN_BASEDIR."/config/usages.json");

$context->httpResponse()
    ->body($content)
    ->send();
