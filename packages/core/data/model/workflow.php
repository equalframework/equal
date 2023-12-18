<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => 'Returns the schema of the workflow defined for the given entity, if any.',
    'help'          => 'This controller provides a JSON map of the workflow as defined in the class of the given entity. If no `getWorkflow` method is found in the class, it falls back to the one defined in the orm\Model class, which returns an empty array.',
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string',
            'usage'         => 'orm/entity',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => [ 'context', 'orm' ]
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 */
list($context, $orm) = [ $providers['context'], $providers['orm'] ];

// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

if(!method_exists($entity, 'getWorkflow')) {
    throw new Exception("missing_method", QN_ERROR_INVALID_CONFIG);
}

$workflow = $entity->getWorkflow();

if(!is_array($workflow)) {
    throw new Exception("invalid_method", QN_ERROR_INVALID_CONFIG);
}

$context->httpResponse()
        ->body($workflow)
        ->send();
