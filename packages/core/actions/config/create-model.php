<?php

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
            'description'   => 'parent model',
            'type'          => 'string',
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
if(!file_exists("packages/{$package}/views")) {
    throw new Exception('missing_views_dir', QN_ERROR_INVALID_CONFIG);
}

$model_path = str_replace("\\","/",$params['model']);

$parts = explode("/",$model_path);

$name = array_pop($parts);

$namespace = implode("\\",$parts);

if(!isFirstLetterUppercase($name)){
    throw new Exception('model_naming_convention_not_respected', QN_ERROR_INVALID_PARAM);
}

// Verification de la non existence du model
$file = QN_BASEDIR."/packages/{$package}/classes/{$model_path}.class.php";

if(file_exists($file)) {
    throw new Exception('model_already_exists', QN_ERROR_INVALID_PARAM);
}


$extends = $params['extends'];
$import = "";

// Verification du model parent
if(strcmp($extends,"Model")===0) {
    $import .= "use equal\orm\Model;\n";
} else {
    $part = explode("\\",$extends);
    $package_parent = array_shift($part);
    $model_parent_path = implode("/",$part);
    $file_parent = QN_BASEDIR."/packages/{$package_parent}/classes/{$model_parent_path}.class.php";
    if(!file_exists($file_parent)) {
        throw new Exception('parent_does_not_exists', QN_ERROR_INVALID_PARAM);
    }
    $extends = "\\".$extends;
}

// création de l'arboréscence de fichier manquante
$dir = QN_BASEDIR."/packages/{$package}/classes/".str_replace("\\","/",$namespace);
$d = mkdir($dir,0777,true);

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

fputs($f,$template);

fclose($f);

$result = file_get_contents($file);

$context->httpResponse()
        ->status(201)
        ->body($result)
        ->send();


function isFirstLetterUppercase($str) {
    // Check if the string is not empty
    if (empty($str)) {
        return false;
    }

    // Get the first character of the string
    $firstChar = mb_substr($str, 0, 1);

    // Check if the first character is uppercase
    return ctype_upper($firstChar);
}