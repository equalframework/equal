<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2025
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, NodeFinder};
use equal\orm\Entity;
list($params, $providers) = eQual::announce([
    'description'   => "create a new policy",
    'help'          => "This controller relies on the PHP binary. Ensure the PHP binary is present in the PATH.",
    'params'        => [
        'entity'    => [
            'description'   => 'Name of the entity (class).',
            'type'          => 'string',
            'required'      => true
        ],
        'name'    => [
            'description'   => 'Name of  the policy',
            'type'          => 'string',
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
$new_policy = $params['name'];
// force class autoload
$entityInstance = $orm->getModel($entity->getFullName());
if(!$entityInstance) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
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


$context->httpResponse()
        ->status(204)
        ->send();
