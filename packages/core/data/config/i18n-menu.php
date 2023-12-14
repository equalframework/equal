<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Retrieves the translation values related to the specified menu.",
    'params'        => [
        'package' =>  [
            'description'   => "Package name the menu relates to (e.g. 'core').",
            'type'          => 'string',
            'required'      => true
        ],
        'menu_id'  => [
          'description'     => "Full menu identifier (menu.<ID>)",
          'type'            => 'string',
          'required'        => true
        ],
        'lang' =>  [
            'description'   => "Language for which values are requested (iso639 code expected).",
            'type'          => 'string',
            'default'       => constant('DEFAULT_LANG')
        ]
    ],
    'constants'     => ['DEFAULT_LANG'],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context  $context
 */
list($context) = [ $providers['context'] ];

$result = [];

$file = QN_BASEDIR."/packages/{$params['package']}/i18n/{$params['lang']}/menu.{$params['menu_id']}.json";

// #memo - to prevent untimely log entries, this script always return a non-404 error
if(file_exists($file) && ($schema = json_decode(@file_get_contents($file), true)) !== null) {
    $result = $schema;
}

$context->httpResponse()
        ->body($result)
        ->send();
