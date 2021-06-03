<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
list($params, $providers) = announce([
    'description'   => "Returns the schema of given class (model)",
    'params'        => [
                        'package' => [
                            'description'   => 'Name of the package for which the schema is requested',
                            'type'          => 'string',
                            'required'      => true
                        ]
    ],
    'response'      => [
        'content-type'  => 'text/xml',
        'charset'       => 'utf-8',
        'accept-origin' => '*',
        'cacheable'     => true
    ],
    'providers'     => ['context', 'orm'] 
]);


list($context, $orm) = [$providers['context'], $providers['orm']];


$json = run('get', 'config_classes', ['package' => $params['package']]);
$classes = json_decode($json, true);

    
$src = <<<EOD
<?xml version="1.0" encoding="utf-8" ?>
<sql>
<datatypes db="mysql">
    <group label="Numeric" color="rgb(238,238,170)">
        <type label="Boolean" quote="" sql="TINYINT" length="4"/>
        <type label="Integer" quote="" sql="INT" length="11"/>
        <type label="Float" quote="" sql="DOUBLE" length="10,2" re="DOUBLE"/>
    </group>
    <group label="Character" color="rgb(255,200,200)">
        <type label="String" quote="'" sql="VARCHAR" length="255"/>
        <type label="Text" quote="'" sql="MEDIUMTEXT" length="0" re="TEXT"/>
    </group>
    <group label="Binary" color="rgb(170,238,238)">
        <type label="Binary" quote="" sql="MEDIUMBLOB" length="0" re="BLOB"/>
    </group>        
    <group label="Date &amp; Time" color="rgb(200,255,200)">
        <type label="Time" quote="" sql="TIME" length="0"/>
        <type label="Datetime" quote="" sql="DATETIME" length="0"/>
    </group>
    <group label="Relational" color="rgb(200,200,255)">
        <type label="Relational" quote="" sql="BIGINT" length="11"/>
    </group>
</datatypes>
</sql>
EOD;

 

$types_associations = array(
	'boolean' 		=> 'TINYINT(4)',
	'integer' 		=> 'INT(11)',
	'float' 		=> 'DECIMAL(10,2)',
    
	'string' 		=> 'VARCHAR(255)',
	'text' 			=> 'MEDIUMTEXT',
	'html' 			=> 'MEDIUMTEXT',    
    
	'date' 			=> 'DATETIME',
	'time' 			=> 'TIME',
	'datetime' 		=> 'DATETIME',
    
	'file' 		    => 'MEDIUMBLOB',    
	'binary' 		=> 'MEDIUMBLOB',
    
	'many2one' 		=> 'BIGINT(11)',
	'many2many'  	=> 'BIGINT(11)'    // convention : add/update  rel_table
);


// XML root
$xml = new SimpleXMLElement($src);


$relations = array();

foreach($classes as $class) {

    $class_name = $params['package'].'\\'.$class;
    $model = $orm->getModel($class_name);
    if(!is_object($model)) throw new Exception("unknown class '{$class_name}'", QN_ERROR_UNKNOWN_OBJECT);
    // get the complete schema of the object (including special fields)
    $schema = $model->getSchema();


    $new_table = $xml->addChild('table');
    $new_table->addAttribute('name', $class_name);	

    foreach($schema as $field => $definition) {
        
        $type = $definition['type'];        
        
        if(in_array($type, ['one2many', 'alias'])) {
            // skip for now
            continue;
        }

        $new_row = $new_table->addChild('row');        

        if($type == 'function') {
            $type = $definition['result_type'];
        }
        else if(in_array($type, ['many2one', 'many2many'])) {
            $new_relation = $new_row->addChild('relation');            
            
            if($type == 'many2many') {
                // create a new relation table
                if(!isset($relations[$definition['rel_table']])) {
                    $relations[$definition['rel_table']] = true;
                    /* append table to XML doc */                                        
                    $new_rel_table = $xml->addChild('table');
                    $new_rel_table->addAttribute('name', $definition['rel_table']);
                    
                    $new_rel_row = $new_rel_table->addChild('row');
                    $new_rel_row->addAttribute('name', $definition['rel_foreign_key']);
                    $new_rel_row->addAttribute('null', 0);
                    $new_rel_row->addAttribute('autoincrement', 0);
                    $new_rel_row->addChild('datatype', $types_associations['many2one']);
                    $new_rel_row->addChild('default', 'NULL');

                    $new_rel_row = $new_rel_table->addChild('row');
                    $new_rel_row->addAttribute('name', $definition['rel_local_key']);
                    $new_rel_row->addAttribute('null', 0);
                    $new_rel_row->addAttribute('autoincrement', 0);
                    $new_rel_row->addChild('datatype', $types_associations['many2one']);
                    $new_rel_row->addChild('default', 'NULL');
                    
        
                    $new_key = $new_rel_table->addChild('key');
                    $new_key->addAttribute('type', 'PRIMARY');
                    $new_key->addAttribute('name', '');
                    $new_key->addChild('part', $definition['rel_foreign_key']);
                    $new_key->addChild('part', $definition['rel_local_key']);    
                }
                // create relation node
                $new_relation->addAttribute('table', $definition['rel_table']);
                $new_relation->addAttribute('row', $definition['rel_local_key']);
            }
            else {
                // create relation node                
                $new_relation->addAttribute('table', $definition['foreign_object']);
                $new_relation->addAttribute('row', 'id');
            }            
        }        

        $new_row->addAttribute('name', $field);
        $new_row->addAttribute('null', 0);
        $new_row->addAttribute('autoincrement', 0);
        $new_row->addChild('datatype', $types_associations[$type]);
        $new_row->addChild('default', 'NULL');
    }
        
    $new_key = $new_table->addChild('key');
    $new_key->addAttribute('type', 'PRIMARY');
    $new_key->addAttribute('name', '');
    $new_key->addChild('part', 'id');

}


$result = $xml->asXML();

$context->httpResponse()->body($result, true)->send();