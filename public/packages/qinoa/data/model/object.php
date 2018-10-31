<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;


list($params, $providers) = QNLib::announce([
    'description'	=>	"Returns values map of the specified fields for object matching given class and identifier.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],    
    'params' 		=>	[
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'id' =>  [
            'description'   => 'Unique identifier of the object to browse.',
            'type'          => 'integer',
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Requested fields. If not specified, only \'id\' and \'name\' fields are returned.',
            'type'          => 'array', 
            'default'       => ['id', 'name']
        ],
        'lang' =>  [
            'description'   => 'Language in which multilang field have to be returned (2 letters ISO 639-1).',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]
    ],    
    'providers'     => ['context', 'orm']     
/*
    Access control is handled inside the qinoa\orm\Collection class
    Default Access controller and Authentication manager can be overriden this way:
    'providers'     => [
        'auth' => custom\AuthenticationManeger, 
        'access' => custom\AccessController,
        ...
    ]         
*/    
]);

list($context, $orm) = [ $providers['context'], $providers['orm'] ];


if(!class_exists($params['entity'])) {
    throw new Exception("unknown class '{$params['entity']}'", QN_ERROR_UNKNOWN_OBJECT);
}

$object = $params['entity']::id($params['id'])
                               ->read($params['fields'], $params['lang'])
                               ->adapt('txt')
                               ->first();

$context->httpResponse()
        ->body($object)
        ->send();