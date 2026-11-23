<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Returns the policies defined for the given entity, if any.',
    'help'          => 'This controller provides a JSON map of the policies as defined in the class of the given entity. If no `getWorkflow` method is found in the class, it falls back to the one defined in the orm\Model class, which returns an empty array.',
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'usage'         => 'orm/entity',
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
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
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
['context' => $context, 'orm' => $orm] = $providers;

// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

if(!method_exists($entity, 'getPolicies')) {
    throw new Exception("missing_method", EQ_ERROR_INVALID_CONFIG);
}

$policies = $entity->getPolicies();

if(!is_array($policies)) {
    throw new Exception("invalid_method", EQ_ERROR_INVALID_CONFIG);
}

$context
    ->httpResponse()
    ->body($policies)
    ->send();
