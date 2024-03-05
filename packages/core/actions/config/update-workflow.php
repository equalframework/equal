<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, NodeFinder};

list($params, $providers) = eQual::announce([
    'description'   => "Translate a workflow definition of a given entity to a PHP method and store it in related file.",
    'help'          => "This controller rely on the PHP binary. In order to make them work, sure the PHP binary is present in the PATH.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'entity'    => [
            'description'   => 'Name of the entity (class).',
            'type'          => 'string',
            'required'      => true
        ],
        'payload' =>  [
            'description'   => 'Workflow definition.',
            'type'          => 'array',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm']
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

$getColumns = null;
$code_php = null;

// Decode the JSON string to a PHP object
$code_php = $params['payload'];
// Get a string representation from the code_php variable, with backslashes escaped
$code_string = str_replace("\\\\", "\\", var_export($code_php, true));
// Create a virtual file holing the default structure with the parsed code and generate a minimal AST
$ast_temp = $parser->parse("<?php \nclass temp { public static function getWorkflow() {return ".$code_string.";}}");
// Find the getWorkflow node in the AST of the temporary file
$nodeGetWorkflow = $nodeFinder->findFirst(
    $ast_temp,
    function(Node $node) {
        return isset($node->name->name)
            && $node->name->name === "getWorkflow";
    }
);

// Add a visitor hijack the getWorkflow method and update its content with new schema
$traverser->addVisitor(
        new class($nodeGetWorkflow, "getWorkflow") extends NodeVisitorAbstract {
            private $node;
            private $target;
            public function __construct($node, $target) {
                $this->node = $node;
                $this->target = $target;
            }
            public function leaveNode(Node $node) {
                if (
                    isset($node->name->name)
                    && $node->name->name === $this->target
                ) {
                    return $this->node;
                }

                return null;
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
if(file_put_contents($file, $result) === false) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
}

try {
    // apply coding standards (ecs.php is expected in QN_BASEDIR)
    $command = 'php ./vendor/bin/ecs check "' . str_replace('\\', '/', $file) . '" --fix';
    if(exec($command) === false) {
        throw new Exception('command_failed', QN_ERROR_UNKNOWN);
    }
    $result = file_get_contents($file);
}
catch(Exception $e) {
    trigger_error("PHP::unable to beautify rendered file ($file): ".$e->getMessage(), QN_REPORT_INFO);
}

$context->httpResponse()
        ->status(204)
        ->body($result)
        ->send();
