<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = eQual::announce([
    'description'   => "Add an empty workflow to the given class by creating a `getWorkflow()` method (if not defined yet).",
    'params'        => [
        'entity' =>  [
            'description'   => 'Full name (including namespace) of the class to look into (e.g. \'core\\User\').',
            'type'          => 'string',
            'required'      => true
        ]
    ],
    'response'      => [
        'content-type'  => 'text',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context', 'orm']
]);

/**
 * @var \equal\php\Context               $context
 * @var \equal\orm\ObjectManager         $orm
 */
list($context, $orm) = [ $providers['context'], $providers['orm'] ];

// force class autoload
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

$class = new ReflectionClass($entity::getType());
if($class->getMethod('getWorkflow')->class == $entity::getType()) {
    throw new Exception("duplicate_method", QN_ERROR_INVALID_PARAM);
}

$file = $class->getFileName();
$code = file_get_contents($file);

// generate replacement code for the getWorkflow method
$workflow_code = ''.
    "    public static function getWorkflow() {\n".
    "        return [];\n".
    "    }\n".
    "\n";

// find the closing curly-bracket of the class
$pos = strrpos($code, '}');

if($pos === false) {
    throw new Exception('malformed_file', QN_ERROR_UNKNOWN);
}

$result = substr_replace($code, $workflow_code, $pos, 0);

// write back the code to the source file
if(file_put_contents($file, rtrim($result)."\n") === false) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(204)
        ->send();
