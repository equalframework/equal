<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => "Attempts to create a new package using a given name.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package of the new model',
            'required'      => true
        ],
        'model' => [
            'type'          => 'string',
            'description'   => 'Name of the model to be created (must be unique).',
            'required'      => true
        ],
        'extends' => [
            'type'          => 'string',
            'description'   => 'Parent class the model inherits from, if any.',
            'default'       => 'Model'
        ]
    ],
    'access'        => [
        'visibility'    => 'protected',
        'groups'        => ['admins']
    ],
    'response'      => [
        'content-type'  => 'text',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'providers'     => ['context', 'orm', 'access']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

$package = $params['package'];

if(!file_exists("packages/{$package}")) {
    throw new Exception('missing_package_dir', EQ_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/classes")) {
    throw new Exception('missing_classes_dir', EQ_ERROR_INVALID_CONFIG);
}

$model_path = $params['model'];

$parts = explode("\\", $model_path);

$name = array_pop($parts);


$namespace = implode("\\", $parts);

$nest = str_replace("\\","/",$namespace);


if(strlen($name) <= 0){
    throw new Exception('empty_model_name', EQ_ERROR_INVALID_PARAM);
}

if(!ctype_upper(mb_substr($name, 0, 1))){
    throw new Exception('broken_naming_convention', EQ_ERROR_INVALID_PARAM);
}

// Verification de la non existence du model
$file = EQ_BASEDIR."/packages/{$package}/classes/{$nest}/{$name}.class.php";

if(file_exists($file)) {
    throw new Exception('model_already_exists', EQ_ERROR_INVALID_PARAM);
}


$extends = $params['extends'];
$import = "";

// Verification du model parent
if(strcmp($extends, "Model") === 0) {
    $import .= "use equal\orm\Model;\n";
}
else {
    $part = explode("\\",$extends);
    $package_parent = array_shift($part);
    $model_parent_path = implode("/",$part);
    $file_parent = EQ_BASEDIR."/packages/{$package_parent}/classes/{$model_parent_path}.class.php";
    if(!file_exists($file_parent)) {
        throw new Exception('parent_does_not_exists', EQ_ERROR_INVALID_PARAM);
    }
    $extends = "\\".$extends;
}

// create folder if missing
$dir = EQ_BASEDIR."/packages/{$package}/classes/".$nest;
$d = mkdir($dir, 0777, true);

$f = fopen($file,"w");

if(!$f) {
    throw new Exception('io_error', EQ_ERROR_UNKNOWN);
}

if(!empty($namespace)) {
    $namespace = "\\".$namespace;
}

$template = <<<EOT
<?php
namespace {$package}{$namespace};
{$import}

class $name extends {$extends} {
    public static function getColumns() {
        return [];
    }
}
EOT;

fputs($f, $template);
fclose($f);

$result = file_get_contents($file);

$context
    ->httpResponse()
    ->status(201)
    ->send();
