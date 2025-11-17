<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Field;
use equal\orm\ObjectManager;

list($params, $providers) = eQual::announce([
    'description'   => "Returns the schema of given class (model) in JSON",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to return (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ],
        'domain' => [
            'description'   => 'Criterias that results have to match (series of conjunctions)',
            'type'          => 'array',
            'default'       => []
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 * @var \equal\data\DataAdapterProvider  $dap
 */
list($context, $orm, $dap) = [$providers['context'], $providers['orm'], $providers['adapt']];

/** @var \equal\data\adapt\DataAdapter */
$adapter = $dap->get('json');


$data = [];

// handle controller entities
$parts = explode('\\', $params['entity']);
$file = array_pop($parts);
if(ctype_lower(substr($file, 0, 1))) {
    $package = array_shift($parts);
    $path = implode('/', $parts);
    $operation = str_replace('\\', '_', $params['entity']);

    try {
        // #memo in case of announce as root (required for relaying params), an Exception with code 0 is raised and JSON is directly sent to stdout
        ob_start();
        if(file_exists(EQ_BASEDIR."/packages/{$package}/actions/{$path}/{$file}.php")) {
            config\eQual::run('do', $operation, array_merge($params, ['announce' => true]), true);
        }
        elseif(file_exists(EQ_BASEDIR."/packages/{$package}/data/{$path}/{$file}.php")) {
            config\eQual::run('get', $operation, array_merge($params, ['announce' => true]), true);
        }
        else {
            throw new Exception("unknown_entity", EQ_ERROR_UNKNOWN_OBJECT);
        }
    }
    catch( Exception $e) {
        if($e->getCode() != 0) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    finally {
        $json = ob_get_clean();
        $result = json_decode($json, true);
        $data = [
            'fields' => isset($result['announcement']['params']) ? $result['announcement']['params'] : []
        ];
        foreach($data['fields'] as $field => $descriptor) {
            if(isset($descriptor['default'])) {
                $f = new Field($descriptor, $field);
                $data['fields'][$field]['default'] = $adapter->adaptOut($descriptor['default'], $f->getUsage());
            }
        }
    }
}

// entity did not relate to a controller
if(!count($data)) {
    $model = $orm->getModel($params['entity']);
    if(!is_object($model)) {
        throw new Exception("unknown_entity", QN_ERROR_UNKNOWN_OBJECT);
    }
    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();

    // retrieve parent class
    $data = [
        'name'          => $model->getName(),
        'description'   => $model->getDescription(),
        'parent'        => get_parent_class($model),
        'root'          => ObjectManager::getObjectRootClass($params['entity']),
        'table'         => $model->getTable(),
        'link'          => $model->getLink(),
        'fields'        => $schema
    ];

    if(method_exists($model, 'getUnique')) {
        $data['unique'] = $model->getUnique();
    }

    if(method_exists($model, 'getDefaults')) {
        $defaults = $model->getDefaults();
        foreach($defaults as $field => $default_value) {
            if( (is_string($default_value) || is_object($default_value)) && is_callable($default_value)) {
                // either a php function (or a function from the global scope) or a closure object
                if(is_object($default_value)) {
                    // default is a closure
                    $default_value = $default_value();
                }
            }
            elseif(is_string($default_value) && strpos($default_value, '::')) {
                list($class_name, $method_name) = explode('::', $default_value);
                if(method_exists($class_name, $method_name)) {
                    /** @var \equal\orm\ObjectManager */
                    $orm = $container->get('orm');
                    $default_value = $orm->callonce($class_name, $method_name);
                }
            }
            $f = $model->getField($field);
            $data['fields'][$field]['default'] = $adapter->adaptOut($default_value, $f->getUsage());
        }
    }

}


$context->httpResponse()
        ->body($data)
        ->send();
