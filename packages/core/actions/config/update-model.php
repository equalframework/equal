<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, NodeFinder, NodeDumper, PrettyPrinter, BuilderFactory, Comment};
use equal\orm\Model;

list($params, $providers) = eQual::announce([
    'description'   => "Translate an entity definition to a PHP file and store it in related package dir.",
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
            'description'   => 'Entity definition .',
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
$code_php = sanitizeColumns($params['payload']['fields']);
// Get a string representation from the code_php variable, with backslashes escaped
$code_string = str_replace("\\\\", "\\", var_export($code_php, true));
// Create a virtual file holing the default structure with the parsed code and generate a minimal AST
$ast_temp = $parser->parse("<?php \nclass temp { public static function getColumns() {return ".$code_string.";}}");
// Find the getColumns node in the AST of the temporary file
$nodeGetColumns = $nodeFinder->findFirst($ast_temp, function(Node $node) {return $node->name->name === "getColumns";});

// Add a visitor hijack the getColumns method and update its content with new schema
$traverser->addVisitor(
        new class($nodeGetColumns, "getColumns") extends NodeVisitorAbstract {
            private $node;
            private $target;
            public function __construct($node, $target) {
                $this->node = $node;
                $this->target = $target;
            }
            public function leaveNode(Node $node) {
                if ($node->name->name === $this->target) {
                    return $this->node;
                }
            }
        }
    );

// Add a visitor for setting the doc comments on the class node
$traverser->addVisitor(
        new class($code_php) extends NodeVisitorAbstract {
            private $code_php;
            public function __construct($code_php) {
                $this->code_php = $code_php;
            }
            public function leaveNode(Node $node) {
                if ($node instanceof Node\Stmt\Class_) {
                    $node->setDocComment(new Comment\Doc(getPropertiesAsComments($this->code_php)));
                    return NodeTraverser::STOP_TRAVERSAL;
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

/**
 * Filter out the properties of the schema from special columns inherited from the Model.
 *
 * @param array     $properties     The new schema, with all the properties even those that are already in th Model
 *
 * @return array    A new array without the properties inherited from Model.
 */
function sanitizeColumns($properties) {
    $result = [];
    $special_columns = Model::getSpecialColumns();
    foreach($properties as $property => $descriptor) {
        if(!array_key_exists($property, $special_columns)){
            $result[$property] = $descriptor;
        }
        elseif($descriptor !== $special_columns[$property]){
            $result[$property] = $descriptor;
        }
    }
    return $result;
}

/**
 * Generate the PHP comments for the properties of the model.
 *
 * @param   array   The properties which need to be in the doc.
 * @return  string  PHP Doc string.
 */
function getPropertiesAsComments($properties) {
    $comment = "/**\n";
    foreach(array_keys($properties) as $property) {
        $comment .= "* @property ";
        switch ($properties[$property]['type']) {
            case 'one2many':
                $comment .= 'array';
                break;
            case 'many2one':
                $comment .= 'integer';
                break;
            case 'many2many':
                $comment .= 'array';
                break;
            case 'computed' :
                $comment .= $properties[$property]['result_type'];
                break;
            default :
                $comment .= $properties[$property]['type'];
                break;
        }
        $comment .= " $" . $property;
        if($properties[$property]['description']){
            $comment .= " " . $properties[$property]['description'];
        }
        $comment .= "\n";
    }
    $comment .= "*/";
    return $comment;
}
