<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
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
    'providers'     => ['context', 'orm']
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];


$file = QN_BASEDIR."/packages/{$params['package']}/i18n/{$params['lang']}/menu.{$params['menu_id']}.json";

if(!file_exists($file)) {
    throw new Exception("unknown_lang_file", QN_ERROR_UNKNOWN_OBJECT);
}

if( ($schema = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_json", QN_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->body($schema)
        ->send();