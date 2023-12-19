<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, NodeFinder, NodeDumper, PrettyPrinter, BuilderFactory, Comment};

list($params, $providers) = eQual::announce([
    'description'   => "Removes the specified view relating to the given entity.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params' => [
        'entity' => [
            'description' => 'Name of the entity.',
            'type' => 'string',
            'required' => true
        ]
    ],
    'access' => [
        'visibility'        => 'protected',
        'groups'            => ['admins']
    ],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context          $context
 * @var \equal\orm\ObjectManager    $orm
 */
list($context, $orm) = [$providers['context'], $providers['orm']];

// Create all the object to use for using PhpParser
$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
$nodeFinder = new NodeFinder;
$traverser = new NodeTraverser;
$prettyPrinter = new PhpParser\PrettyPrinter\Standard;
// Get the parts of the entity string, separated by backslashes
$parts = explode('\\', $params['entity']);
// Get the package name from the first part of the string
$package = array_shift($parts);// Get the package name from the first part of the string
// Get the file name from the last part of the string
$filename = array_pop($parts);
// Get the class path from the remaining part
$class_path = implode('/', $parts);

// Add a visitor hijack the getWorkflow method and update its content with new schema
$traverser->addVisitor(
        new class("getWorkflow") extends NodeVisitorAbstract {
            private $target;
            public function __construct($target) {
                $this->target = $target;
            }
            public function leaveNode(Node $node) {
                if ($node->name->name === $this->target) {
                    return NodeTraverser::REMOVE_NODE;
                }
            }
        }
    );

// Get the full path of the file
$file = QN_BASEDIR."/packages/{$package}/classes/{$class_path}/{$filename}.class.php";
// Get the code from the original file ...
$code = file_get_contents($file);
// ... and parse it to create an AST
$stmtOriginal = $parser->parse($code);
// Update the AST by using visitors attached to the traverser
$stmtModified = $traverser->traverse($stmtOriginal);
// Pretty print the modified AST ...
$result = $prettyPrinter->prettyPrintFile($stmtModified);
// ... and write back the code to the file
file_put_contents($file, $result);

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
