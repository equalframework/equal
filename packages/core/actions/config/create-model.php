<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = eQual::announce([
    'description'   => "Attempts to create a new package using a given name.",
    'params'        => [
        'package' =>  [
            'description'   => 'Name of the package of the new model',
            'type'          => 'string',
            'required'      => true
        ],
        'model' =>  [
            'description'   => 'Name of the model to be created (must be unique).',
            'type'          => 'string',
            'required'      => true
        ],
        'extends' => [
            'type'          => 'string',
            'description'   => 'Parent class the model inherits from, if any.',
            'default'       => 'Model'
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
    'providers'     => ['context', 'orm', 'access']
]);

$package = $params['package'];

if(!file_exists("packages/{$package}")) {
    throw new Exception('missing_package_dir', QN_ERROR_INVALID_CONFIG);
}
if(!file_exists("packages/{$package}/classes")) {
    throw new Exception('missing_classes_dir', QN_ERROR_INVALID_CONFIG);
}

$model_path = $params['model'];

$parts = explode("\\", $model_path);

$name = array_pop($parts);


$namespace = implode("\\", $parts);

$nest = str_replace("\\","/",$namespace);


if(strlen($name) <= 0){
    throw new Exception('empty_model_name', QN_ERROR_INVALID_PARAM);
}

if(!ctype_upper(mb_substr($name, 0, 1))){
    throw new Exception('broken_naming_convention', QN_ERROR_INVALID_PARAM);
}

// Verification de la non existence du model
$file = QN_BASEDIR."/packages/{$package}/classes/{$nest}/{$name}.class.php";

if(file_exists($file)) {
    throw new Exception('model_already_exists', QN_ERROR_INVALID_PARAM);
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
    $file_parent = QN_BASEDIR."/packages/{$package_parent}/classes/{$model_parent_path}.class.php";
    if(!file_exists($file_parent)) {
        throw new Exception('parent_does_not_exists', QN_ERROR_INVALID_PARAM);
    }
    $extends = "\\".$extends;
}

// create folder if missing
$dir = QN_BASEDIR."/packages/{$package}/classes/".$nest;
$d = mkdir($dir, 0777, true);

$f = fopen($file,"w");

if(!$f) {
    throw new Exception('io_error', QN_ERROR_UNKNOWN);
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

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();
