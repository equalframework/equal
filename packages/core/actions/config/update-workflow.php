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

// force class autoload
$entity = $orm->getModel($params['entity']);
if(!$entity) {
    throw new Exception("unknown_entity", QN_ERROR_INVALID_PARAM);
}

$class = new ReflectionClass($entity::getType());

if(!$class->hasMethod('getWorkflow')) {
    throw new Exception('missing_workflow', QN_ERROR_UNKNOWN);
}

$getWorkflow = new ReflectionMethod($params['entity'], 'getWorkflow');

// retrieve content of the source file of the targeted entity
$file = $class->getFileName();
$code = file_get_contents($file);
$lines = explode("\n", $code);

// retrieve start and end lines of getWorkflow declaration
$start_index = $getWorkflow->getStartLine() - 1;
$end_index = $getWorkflow->getEndLine() - 1;

// generate replacement code for the getWorkflow method
$workflow_code = ''.
    "    public static function getWorkflow() {\n".
    "        return ".array_export($params['payload'], 4, 2, true).";\n".
    "    }";

$result = '';

foreach($lines as $index => $line) {
    if($index < $start_index) {
        $result .= $line."\n";
    }
    else {
        if($index == $start_index) {
            $result .= $workflow_code."\n";
        }
        else {
            if($index > $end_index) {
                $result .= $line."\n";
            }
        }
    }
}

// write back the code to the source file
if(file_put_contents($file, rtrim($result)."\n") === false) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
}

$context->httpResponse()
        ->status(204)
        ->send();

/**
 * Use var_export() native function and apply a few regex replacements to improve the syntax.
 * Note : this strategy is preferred to the use of PhpParser since it allows to modify only the portion of code targeted by the getWorkflow method.
 *
 * @param array     $array                  Array to be converted to PHP code.
 * @param int       $indent_spaces          Number of spaces to use for code indentation.
 * @param int       $pad_indents            Number of indents each line must be prefixed with.
 * @param bool      $ignore_first_indent    Flag for disabling indent for first line.
 */
function array_export($array, $indent_spaces = 4, $pad_indents = 0, $ignore_first_indent=false) {
    // convert to PHP code
    $export = var_export($array, true);
    // minimalist code 'beautify'
    $patterns = [
        // convert to square bracket notation
        "/array \(/"                        => '[',
        "/^([ ]*)\)(,?)$/m"                 => '$1]$2',
        "/=>[ ]?\n[ ]+\[/"                  => '=> [',
        "/([ ]*)(\'[^\']+\') => ([\[\'])/"  => '$1$2 => $3',
        // remove explicit numeric index
        "/[0-9]+ => /"                      => ''
    ];
    $result = preg_replace(array_keys($patterns), array_values($patterns), $export);
    // indent lines
    $lines = explode("\n", $result);
    foreach($lines as $index => $line) {
    	if(!$ignore_first_indent || $index > 0) {
            $code = ltrim($line);
            // produced PHP code is 2 spaces indented
            $indents = (strlen($line) - strlen($code)) / 2;
    		$lines[$index] = str_pad('', $pad_indents*$indent_spaces, ' ').
                             str_pad('', $indents*$indent_spaces, ' ').
                             $code;
    	}
    }
    return implode("\n", $lines);
}
