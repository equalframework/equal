<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpParser\ParserFactory;

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


// retrieve target entity
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

$reflectionClass = new ReflectionClass($entity::getType());
if($reflectionClass->getMethod('getWorkflow')->class == $entity::getType()) {
    throw new Exception("duplicate_method", QN_ERROR_INVALID_PARAM);
}

$parts = explode('\\', $params['entity']);
// Get the package name from the first part of the string
$package = array_shift($parts);// Get the package name from the first part of the string
// Get the file name from the last part of the string
$class_name = array_pop($parts);
// Get the class path from the remaining part
$class_path = implode('/', $parts);

// Create all the object to use for using PhpParser
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$printer = new PhpParser\PrettyPrinter\Standard;

// Get the full path of the file
$file = QN_BASEDIR."/packages/{$package}/classes/{$class_path}/{$class_name}.class.php";

// get php code from original file ...
$code = file_get_contents($file);

// find the closing curly-bracket of the class
$pos = strrpos($code, '}');

if($pos === false) {
    throw new Exception('malformed_file', QN_ERROR_UNKNOWN);
}

$code = substr_replace($code, 'public static function getWorkflow() {return [];} }', $pos, 1);

// ... and parse it to create an AST
$stmt = $parser->parse($code);

// Pretty print the modified AST ...
$result = $printer->prettyPrintFile($stmt);

// ... and write back the code to the file
if(file_put_contents($file, $result) === false) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
}

try {
    // apply coding standards (ecs.php is expected in QN_BASEDIR)
    $command = 'php ./vendor/bin/ecs check "' . str_replace('\\', '/', $file) . '" --fix';
    if(exec($command) === false) {
        throw new Exception('command_failed', QN_ERROR_UNKNOWN);
    }
}
catch(Exception $e) {
    trigger_error("PHP::unable to beautify rendered file ($file): ".$e->getMessage(), QN_REPORT_INFO);
}

$result = file_get_contents($file);

$context->httpResponse()
        ->body($result)
        ->send();
