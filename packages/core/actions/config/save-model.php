<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, NodeFinder, NodeDumper, PrettyPrinter, BuilderFactory, Comment};
use equal\orm\Model;

list($params, $providers) = announce([
    'description'   => "Translate an entity definition to a PHP file and store it in related package dir.",
    'help'          => "This controller rely on the PHP binary. In order to make them work, sure the PHP binary is present in the PATH.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'entity'	=> [
            'description'   => 'Name of the entity (class).',
            'type'          => 'string',
            'required'      => true
        ],
        'part' => [
            'description' => '',
            'type' => 'string',
            'required'      => true
        ],
        'payload' =>  [
            'description'   => 'Entity definition .',
            'type'          => 'array',
            'required'      => true
        ]
    ],
    'providers'     => ['context', 'orm', 'adapt']
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

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
$file = array_pop($parts);
// Get the class path from the remaining part
$class_path = implode('/', $parts);

$getColumns = null;
$code_php = null;

// If you want to use save-model for updating a class/getColumns
if($params['part'] == 'class') {
    // Decode the JSON string to a PHP object
    $code_php = deleteModelProperty($params['payload']['fields']);

    // Get the string representation of the code_php variable, with backslashes escaped
    $code_string = str_replace("\\\\", "\\", var_export($code_php, true) );

    // Get the full path to the file
    $file = QN_BASEDIR."/packages/{$package}/classes/{$class_path}/{$file}.class.php";

    // #todo - handle class creation in controller create-model
    /*
    // Create a temporary file with the following contents and then parse it to have a ast
    $temp_file = "<?php \nclass temp { public static function getColumns() { return ".$code_string.";}}";
    $ast_temp_file= $parser->parse($temp_file);

    // Find the getColumns node in the AST of the temporary file
    $nodeGetColumns = $nodeFinder->findFirst($ast_temp_file, function(Node $node) {
        return $node->name->name === "getColumns";
    });

    // Add a visitor to the traverse
    $traverser->addVisitor(new class($nodeGetColumns, "getColumns") extends NodeVisitorAbstract {

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
    });
    */

    // Add a visitor to the traverser that will set the doc comment of the class node to the result of the getPropertiesAsComments function
    $traverser->addVisitor(new class($code_php) extends NodeVisitorAbstract {
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
    });
}

// Get the code from the file and parse it in a AST
$code = file_get_contents($file);
$stmts = $parser->parse($code);

// Traverse the AST of the code with the traverser
$modifiedStmts = $traverser->traverse($stmts);

// Pretty print the modified AST and write a pretty code into the file
$result = $prettyPrinter->prettyPrintFile($modifiedStmts);
file_put_contents($file, $result);

$command = 'php ./vendor/bin/ecs check "' . str_replace('\\', '/', $file) . '" --fix';

if(exec($command) === false) {
    throw new Exception('command_failed', QN_ERROR_UNKNOWN);
}

$result = file_get_contents($file);

$context->httpResponse()
        ->body($result)
        ->send();

/**
 * This function deletes the property of the schema that are already inherited from the Model.
 *
 * @param array     $properties     The new schema, with all the properties even those that are already in th Model
 *
 * @return array    A new array without the properties already inherited from Model
 */
function deleteModelProperty($properties) {
    $result = [];
    $specialColumns =  Model::getSpecialColumns();
    foreach($properties as $property => $descriptor) {
        if(array_key_exists($property, $specialColumns)){
            if($descriptor != $specialColumns[$property]){
                if($specialColumns[$property]["type"] != 'datetime') {
                    $result[$property] = $descriptor;
                }
            }
        }
        else {
            $result[$property] = $descriptor;
        }
    }

    return $result;
}

/**
 * This function gets the comments for the properties of the model
 *
 * @param Array the properties which need to be in the doc
 * @return string of the documentation
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