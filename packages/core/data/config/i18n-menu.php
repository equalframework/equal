<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
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

$files = [];
$parts = explode('.', $params['menu_id']);

while(!empty($parts)) {
    $files[] = EQ_BASEDIR."/packages/{$params['package']}/i18n/{$params['lang']}/menu." . implode('.', $parts) . '.json';
    if(strpos($params['lang'], '_') !== false) {
        $language = explode('_', $params['lang'])[0];
        $files[] = EQ_BASEDIR."/packages/{$params['package']}/i18n/{$language}/menu." . implode('.', $parts) . '.json';
    }
    array_pop($parts);
}

foreach($files as $file) {
    if(!file_exists($file)) {
        continue;
    }

    // #memo - to prevent untimely log entries, this script always return a non-404 error
    if(($schema = json_decode(@file_get_contents($file), true)) === null) {
        continue;
    }

    if(empty($result)) {
        $result = $schema;
    }
    else {
        $result = array_replace_recursive($result, $schema);
    }
}

$context->httpResponse()
        ->body($result)
        ->send();
