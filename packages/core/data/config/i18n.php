<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Retrieves the translation values related to the specified entity. This controller is deprecated, use `core_config_translation` instead.",
    'deprecated'    => true,
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Full name (including namespace) of the class to look for (e.g. \'core\\User\').',
            'required'      => true
        ],
        'lang' => [
            'type'          => 'string',
            'description'   => 'Language for which values are requested (iso639 code expected).',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['DEFAULT_LANG'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$result = eQual::run('get', 'translation', $params);

$context
    ->httpResponse()
    ->body($result)
    ->send();
