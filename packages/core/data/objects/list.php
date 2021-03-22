<?php
/**
*    This file is part of the easyObject project.
*    http://www.cedricfrancoys.be/easyobject
*
*    Copyright (C) 2012  Cedric Francoys
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation, either version 3 of the License, or
*    (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
* file: packages/core/data/objects/list.php
*
* Search and browse objects matching the given criteria.
*
* input: 'object_class'
*
* example
* full URL : http://localhost/easyobject/index.php?get=core_objects_list&class=school\Student&fields=id,firstname,lastname,birthdate,subscription&rp=20&page=1&sortname=id&sortorder=asc&domain=[[]]
* output result :
* {
*	"page": "1",
*	"total": "1",
*	"records": "5",
*	"rows": [
*		{"id":"1","cell":["1","Bart","Simpson","1976-03-04","2012-08-25"]},
*		{"id":"2","cell":["2","Parker","Lewis","1971-11-05","2012-08-25"]},
*		{"id":"3","cell":["3","Kevin","McCallister","1980-08-26","2012-08-25"]},
*		{"id":"4","cell":["4","Joey","Jeremiah","1971-12-22","2012-08-25"]},
*		{"id":"5","cell":["5","Todd","Anderson","1970-11-06","2012-08-25"]}
*		]
* }
*
*/

// Dispatcher (index.php) is in charge of setting the context and should include easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

// force silent mode (debug output would corrupt json data)
set_silent(true);

// announce script and fetch parameters values
$params = announce(
	array(
		'description'	=>	"Search and browse objects matching the given criteria.",
		'params' 		=>	array(
								'object_class'	=> array(
													'description' => 'Class to look into.',
													'type' => 'string',
													'required'=> true
													),
								'fields'		=> array(
													'description' => 'Wanted fields. If not specified, all simple fields are returned.',
													'type' => 'array',
													'default' => null
													),
								'domain'		=> array(
													'description' => 'The domain holds the criteria that results have to match (serie of conjunctions)',
													'type' => 'array',
													'default' => array(array())
													),
								'page'		=> array(
													'description' => 'The page we\'re interested in (page length is set with \'rp\' parameter).',
													'type' => 'int',
													'default' => 1
													),
								'rp'		=> array(
													'description' => 'Number of rows we want to have into the list.',
													'type' => 'int',
													'default' => 10
													),
								'sortname'		=> array(
													'description' => 'Column to use for sorting results.',
													'type' => 'string',
													'default' => 'id'
													),
								'sortorder'		=> array(
													'description' => 'The direction  (i.e. \'asc\' or \'desc\').',
													'type' => 'string',
													'default' => 'asc'
													),
								'records'		=> array(
													'description' => 'Number of records in the list (if already known)',
													'type' => 'string',
													'default' => null
													),
								'mode'		=> array(
													'description' => 'Allows to limit result to deleted objects (when value is \'recycle\')',
													'type' => 'string',
													'default' => null
													),
								'lang'			=> array(
													'description '=> 'Specific language for multilang field.',
													'type' => 'string',
													'default' => DEFAULT_LANG
													)
							)
	)
);


$start = ($params['page']-1) * $params['rp'];
if($start < 0) $start = 0;

$list = array();

// 2) check for special option 'mode' (that allows to limit result to deleted objects)
if($params['mode'] == 'recycle') {
	// add the (deleted = 1) clause to every condition
	for($i = 0, $j = count($params['domain']); $i < $j; ++$i)
		$params['domain'][$i] = array_merge($params['domain'][$i], array(array('deleted', '=', '1')));
}

// 3) search and browse
if(empty($params['records'])) {
	// We search all possible results. It might take some time (the bigger the tables, the longer it takes to process them)
	// but it is the only way to determine the number of results,
	// so we do it only when the number of results is unknown.
	$ids = search($params['object_class'], $params['domain'], $params['sortname'], $params['sortorder'], 0, '', $params['lang']);
	if($count_ids = count($ids))
		$list = read($params['object_class'], array_slice($ids, $start , $params['rp'], true), $params['fields'], $params['lang']);
}
else {
	// This is a faster way to do the search but it requires the number of total results.
	$ids = search($params['object_class'], $params['domain'], $params['sortname'], $params['sortorder'], $start, $params['rp'], $params['lang']);
	$list = read($params['object_class'], $ids, $params['fields'], $params['lang']);
	$count_ids = $params['records'];
}

// 4) generate json result
$json = '{'."\n";
$json .= '	"page": "'.$params['page'].'",'."\n";
$json .= '	"total": "'.ceil($count_ids/$params['rp']).'",'."\n";
$json .= '	"records": "'.$count_ids.'",'."\n";
$json .= '	"rows": ['."\n";

foreach($list as $id => $object_fields) {
	$json .= '		{"id":"'.$id.'","cell":[';
	foreach($object_fields as $field_name => $field_value) {
		$json .= json_encode($object_fields[$field_name]).',';
	}
	$json = rtrim($json, ' ,').']},'."\n";
}
$json = rtrim($json, "\n ,");

$json .= "\n".'	]'."\n";
$json .= '}';

// 5) output result
header('Content-type: text/html; charset=UTF-8');
print($json);
