<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use config\QNLib;

list($params, $providers) = QNLib::announce([
    'description'   => "Update (fully or partially) the given object.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string', 
            'required'      => true
        ],
        'id' =>  [
            'description'   => 'Unique identifier of the object to update.',
            'type'          => 'integer',
            'required'      => true
        ],
        'fields' =>  [
            'description'   => 'Associative map of fields to be updated, with their related values.',
            'type'          => 'array', 
            'default'       => []
        ],
        'lang' => [
            'description '  => 'Specific language for multilang field.',
            'type'          => 'string', 
            'default'       => DEFAULT_LANG
        ]
    ],
    'providers'     => ['context', 'orm', 'adapt'] 
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];


$fields = [];
// adapt received values for parameter 'fields' (are still text formated)
$schema = $orm->getModel($params['entity'])->getSchema();
foreach($params['fields'] as $field => $value) {
    $fields[$field] = $adapter->adapt($value, $schema[$field]['type'], 'php', 'txt');
}
// retrieve entity and update it    
$result = $params['entity']::id($params['id'])->update($fields, $params['lang'])->first();

$context->httpResponse()
        ->body($result)
        ->send();