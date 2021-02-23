<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'	=>	"Returns values map of the specified fields for object matching given class and identifier.",
    'params' 		=>	[
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'ids' =>  [
            'description'   => 'List of unique identifierd of the object to read.',
            'type'          => 'array',
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
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context' ]  
]);

list($context, $orm) = [ $providers['context'] ];


if(!class_exists($params['entity'])) {
    throw new Exception("unknown_entity", QN_ERROR_UNKNOWN_OBJECT);
}

$object = $params['entity']::id($params['ids'])->read($params['fields'], $params['lang'])->adapt('txt')->first();

$context->httpResponse()
        ->body($object)
        ->send();