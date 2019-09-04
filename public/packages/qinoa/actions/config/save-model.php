<?php
/*
    This file is part of the qinoa framework <http://www.github.com/cedricfrancoys/qinoa>
    Some Rights Reserved, Cedric Francoys, 2018, Yegen
    Licensed under GNU GPL 3 license <http://www.gnu.org/licenses/>
*/

list($params, $providers) = announce([
    'description'   => "Translate an entity definition to a PHP file and store it in related package dir.",
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'UTF-8',
        'accept-origin' => '*'
    ],
    'params'        => [
        'schema' =>  [
            'description'   => 'Entity definition .',
            'type'          => 'array', 
            'required'      => true

        ],
		'package'	=> [
            'description'   => 'Package in which entity is declared.',
            'type'          => 'string',
            'required'      => true
        ],		
		'entity'	=> [
            'description'   => 'Name of the entity (class).',
            'type'          => 'string',
            'required'      => true
        ],
    ],
    'providers'     => ['context', 'orm', 'adapt'] 
]);

list($context, $orm, $adapter) = [$providers['context'], $providers['orm'], $providers['adapt']];

// quick sanitization
$params['entity'] = ucfirst($params['entity']);
$params['package'] = strtolower($params['package']);

$class_name = $params['package'].'\\'.$params['entity'];
$parent = $params['schema']['parent'];

// normalize fields map
$fields = [];
foreach($params['schema']['fields'] as $fld_item) {
	$fields[$fld_item['name']] = ['type' => $fld_item['type']];
	if(isset($fld_item['attributes'])) {
		foreach($fld_item['attributes'] as $attr_name => $attr) {
			if(in_array($attr_name, ['multilang', 'store'])) {
				$attr['value'] = $adapter->adapt($attr['value'], 'boolean');
				if($attr['value'] === false) continue;
			}
			if($attr_name == 'selection' && empty($attr['value'])) continue;
			$fields[$fld_item['name']][$attr_name] = $attr['value'];
		}
	}
}


// default for parent is 'qinoa\orm\Model'
if($parent != 'qinoa\orm\Model') {	
	$parentModel = $orm->getModel($parent);

	$parent_schema = $parentModel->getSchema();

	// reduce schema to fields exclusive to the declared class (not from ancestors)
	$diff_fields = array_diff(array_keys($fields), array_keys($parent_schema));

	$fields = array_filter($fields, function($k) use($diff_fields) {
		return in_array($k, $diff_fields);
	}, ARRAY_FILTER_USE_KEY);
}


$filename = QN_BASEDIR.'/public/packages/'.$params['package'].'/classes/'.$params['entity'].'.class.php';
echo $filename;

$schema = var_export($fields, true);

$content = <<<EOT
<?php
namespace {$params['package']};


class {$params['entity']} extends \\{$parent} {
    public static function getColumns() {
        return {$schema};
	}
}

EOT;

file_put_contents($filename, $content);

$context->httpResponse()
		->status(204)
        ->body($result)
        ->send();