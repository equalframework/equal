<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
use PhpParser\{Node, NodeTraverser, NodeVisitorAbstract, ParserFactory, NodeFinder, NodeDumper, PrettyPrinter, BuilderFactory, Comment};
use PhpParser\Node\Expr\CallLike;
use equal\orm\Model;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

list($params, $providers) = eQual::announce([
    'description'   => "Translate an entity definition to a PHP file and store it in related package dir.",
    'help'          => "This controller rely on the PHP binary. In order to make them work, sure the PHP binary is present in the PATH.",
    'response'      => [
        'content-type'  => 'text/plain',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'controller'	=> [
            'description'   => 'Name of the controller.',
            'type'          => 'string',
//            'required'      => true
        ],
        'operation'	=> [
            'description'   => 'Operation the controller relates to.',
            'type'          => 'string',
            'selection'     => [
                'do',
                'get',
                'show'
            ]
        ],
        'payload' =>  [
            'description'   => 'Controller `announce` descriptor.',
            'type'          => 'string',
            'usage'         => 'application/json'
//            'required'      => true
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
$params['controller'] = str_replace('_', '\\', $params['controller']);
print($params['controller']."\n");
$parts = explode('\\', $params['controller']);

// Get the package name from the first part of the string
$package = array_shift($parts);
print($package."\n");
// Get the file name from the last part of the string
$filename = array_pop($parts);
// Get the class path from the remaining part
$class_path = implode('/', $parts);

print($params['payload']);

if(!($decoded = json_decode($params['payload'],true))) {
    throw new Exception('Malformed Json', QN_ERROR_INVALID_PARAM);
}

// Get a string representation from the code_php variable, with backslashes escaped
$code_string = str_replace("\\\\", "\\", var_export( $decoded, true));

// #test #toremove
// $code_string = "[
//     'description'   => \"save a representation of a view to a json file\",
//     'response'      => [
//         'content-type'  => 'text/plain',
//         'charset'       => 'UTF-8',
//         'accept-origin' => '*',
//         'schema' => [
//             'type'   => 'entity',
//             'qty'    => 'one',
//             'entity' => 'core\User',
//             'values' => []
//         ]
//     ],
//     'params' => [
//         'entity' => [
//             'description' => 'name of the entity',
//             'type' => 'string',
//             'required' => true
//         ],
//         'view_id' => [
//             'description' => 'id of the view',
//             'type' => 'string',
//             'required' => true
//         ],
//     ],
//     'access' => [
//         'visibility'        => 'protected',
//         'groups'            => ['admins']
//     ],
//     'providers'     => ['context']
//     ]";

// Create a virtual file holding the default structure, and generate a minimal AST
$ast_temp = $parser->parse("<?php \nlist(\$params, \$providers) = eQual::announce($code_string);");


/** @var Node $node */
$node = $nodeFinder->findFirst($ast_temp, function(Node $node) {
    // support for `announce`
    if(get_class($node) == 'PhpParser\Node\Expr\FuncCall' && property_exists($node, 'name') && get_class($node->name) == 'PhpParser\Node\Name' && $node->name->getFirst() == 'announce') {
        return true;
    }
    // support for `eQual::announce`
    if(get_class($node) == 'PhpParser\Node\Expr\StaticCall' && property_exists($node, 'class') && get_class($node->class) == 'PhpParser\Node\Name' && $node->class->getFirst() == 'eQual') {
        return property_exists($node, 'name') && get_class($node->name) == 'PhpParser\Node\Identifier' && $node->name->name == 'announce';
    }
    return false;
});

// Add a visitor to the traverse in order to consider the getColumns method and update its content with new schema
$traverser->addVisitor(
        new class($node) extends NodeVisitorAbstract {
            private $node;

            public function __construct($node) {
                $this->node = $node;
            }

            public function leaveNode(Node $node) {
                if ($node->name->name === 'announce') {
                    return $this->node;
                }
            }
        }
    );

// Get the full path of the file
$dir = ['do' => 'actions', 'get' => 'date', 'show' => 'apps'][$params['operation']];
$file = QN_BASEDIR."/packages/{$package}/{$dir}/{$class_path}/{$filename}.php";
// Get the code from the original file ...
$code = file_get_contents($file);
// ... and parse it to create an AST
$stmtOriginal = $parser->parse($code);


// #test #toremove fake original file
// $stmtOriginal = $parser->parse("<?php \nlist(\$params, \$providers) = eQual::announce([]);
// /**
//  * @var \\equal\\php\\Context  \$context
//  */
// list(\$context) = [\$providers['context']];

// \$parts = explode(\"\\\\\",\$params['entity']);
// \$parts_v = explode(\".\",\$params['view_id']);
// ");

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