<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Returns an associative array mapping orm types with possible widget types.',
    'help'          => 'Widget types allow variations on the way the widget is displayed, without impacting the underlying value (whose type depends on the type and usage from the related field).',
    'params'        => [    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

if(!file_exists(EQ_BASEDIR."/config/widget_types.json")) {
    throw new Exception("no_usages_file", EQ_ERROR_UNKNOWN);
}

$content = file_get_contents(EQ_BASEDIR."/config/widget_types.json");

$context
    ->httpResponse()
    ->body($content)
    ->send();
