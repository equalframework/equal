<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Returns a list of timezones identifiers, filtered by keywords if given.',
    'params'        => [
        'q' => [
            'type'          => 'string',
            'description'   => 'Keyword for filtering matching timezones.',
            'default'       => ''
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => [ 'context' ]
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$result = [];

$list = DateTimeZone::listIdentifiers();

if(strlen($params['q'])) {
    foreach($list as $tz) {
        if(stripos($tz, $params['q']) !== false) {
            $result[] = $tz;
        }
    }
}
else {
    $result = $list;
}

$context
    ->httpResponse()
    ->body($result)
    ->send();