<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'   => 'Returns a list of timezones identifiers, filtered by keywords if given.',
    'params'        => [
        'q' =>  [
            'description'   => 'Keyword for filtering matching timezones.',
            'type'          => 'string',
            'default'       => ''
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ]
]);

/**
 * @var \equal\php\Context  $context
 */
list($context) = [ $providers['context'] ];

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

$context->httpResponse()
        ->body($result)
        ->send();