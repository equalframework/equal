<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

use equal\orm\Entity;
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, NodeFinder};
[$params, $providers] = eQual::announce([
    'description'   => "create a new policy",
    'help'          => "This controller relies on the PHP binary. Ensure the PHP binary is present in the PATH.",
    'params'        => [
        'entity' => [
            'type'          => 'string',
            'description'   => 'Name of the entity (class).',
            'required'      => true
        ],
        'name' => [
            'type'          => 'string',
            'description'   => 'Name of  the policy',
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
['context' => $context, 'orm' => $orm] = $providers;


$entity = new Entity($params['entity']);
$new_policy = $params['name'];
// force class autoload
$entityInstance = $orm->getModel($entity->getFullName());
if(!$entityInstance) {
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$method_name = 'getPolicies' ;
$structure= [
'description' =>'',
'function' => ''
];
$class = new ReflectionClass($entityInstance::getType());
if(!($class->getMethod($method_name)->class == $entityInstance::getType())) {
    equal::run('do','core_config_create-policies',['entity' => $entity->getFullName()], true);
}

$entity->updateMethodLine($method_name,$new_policy, $structure);


$context
    ->httpResponse()
    ->status(204)
    ->send();
