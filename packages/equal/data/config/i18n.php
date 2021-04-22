<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Retrieves the translation values related to the specified class.",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look for (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'lang' =>  [
            'description'   => 'Language for which values are requested (iso639 code expected)..',
            'type'          => 'string', 
			'default' 		=> DEFAULT_LANG
        ],
        
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm'] 
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];


$parts = explode('\\', $params['entity']);
$package = array_shift($parts);

$class_dir = implode('/', $parts);
$file = QN_BASEDIR."/packages/{$package}/i18n/{$params['lang']}/{$class_dir}.json";


if(!file_exists($file)) {
    throw new Exception("unknown_lang_file", QN_ERROR_UNKNOWN_OBJECT);
}

if( ($lang = json_decode(@file_get_contents($file), true)) === null) {
    throw new Exception("malformed_json", QN_ERROR_INVALID_CONFIG);
}


$context->httpResponse()
        ->body($lang)
        ->send();