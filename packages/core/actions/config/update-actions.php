<?php

use equal\orm\Entity;

list($params, $providers) = eQual::announce([
    'description'   => "Update the getActions() return function of an entity",
    'help'          => "This controller relies on the PHP binary. Ensure the PHP binary is present in the PATH.",
    'params'        => [
        'entity'    => [
            'description'   => 'Name of the entity (class).',
            'type'          => 'string',
            'required'      => true
        ],
        'payload' =>  [
            'description'   => 'Actions definition.',
            'type'          => 'array',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */

list($context, $orm) = [$providers['context'], $providers['orm']];

$entity = new Entity($params['entity']);

// force class autoload
$model = $orm->getModel($params['entity']);
if(!$model) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$method_name = 'getActions' ;
$class = new ReflectionClass($model::getType());

if(!$class->hasMethod($method_name) || $class->getMethod($method_name)->class !== $model::getType()) {
    equal::run('do','core_config_create-actions',['entity' => $entity->getFullName()], true);
}

$entity->updateMethod($method_name, $params['payload']);

$context->httpResponse()
        ->status(204)
        ->send();