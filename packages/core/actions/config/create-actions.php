<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2024
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Add an empty actions to the given class by creating a `getActions()` method (if not defined yet).",
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
    throw new Exception("unknown_entity", EQ_ERROR_INVALID_PARAM);
}

$class = new ReflectionClass($entity::getType());

if($class->hasMethod('getActions')) {
    if($class->getMethod('getActions')->class == $entity::getType()) {
        throw new Exception("duplicate_method", EQ_ERROR_INVALID_PARAM);
    }
}

$file = $class->getFileName();
$code = file_get_contents($file);

// generate replacement code for the getActions method
$actions_code = ''.
    "    public static function getActions() {\n".
    "        return [];\n".
    "    }\n".
    "\n";

// find the closing curly-bracket of the class
$pos = strrpos($code, '}');

if($pos === false) {
    throw new Exception('malformed_file', EQ_ERROR_UNKNOWN);
}

$result = substr_replace($code, $actions_code, $pos, 0);

// write back the code to the source file
if(file_put_contents($file, rtrim($result)."\n") === false) {
    throw new Exception('io_error', EQ_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(204)
        ->send();
