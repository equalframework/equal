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
* file: packages/core/data/objects/report.php
*
*/

// the dispatcher (index.php) is in charge of setting the context and should include the easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

$params = get_params(array(
							'object_class'		=> null, 
							'fields'			=> null, 
							'operations'		=> null,
							'group_by'			=> null,	
							'domain'			=> array(array()), 
							'sortname'			=> 'id',				// index column (i.e. user click to sort)
							'sortorder'			=> 'asc',				// the direction  (i.e. 'asc' or 'desc')
							'lang'				=> DEFAULT_LANG							
					));

// 1) Generate a 'report-query' based on the parameters	
/*
example : 
	SELECT `customer_name` , sum( `total_repro` ) as total_repro, sum( `total_press_cost` ) as total_press_cost, sum( `total_scr_cost` ) as total_scr_cost
	FROM `linnetts_job`
	WHERE date > '2013-09-01'
	AND date < '2013-12-01'
	GROUP BY `customer_name`
*/


$result = array();
$totals = array();

// workaround for linnetts 
if(empty($params['domain'][0])) {
	// if no domain is specified, we return an empty report
}
else {
	$dbConnection = &DBConnection::getInstance();			

    $schema = get_object_schema($params['object_class']);
	$table = get_object_table_name($params['object_class']);
	$fields = $params['fields'];
	$operations = $params['operations'];			
	$group_by = $params['group_by'];			
				
	$query = "SELECT ";
	foreach($fields as $i => $field) {			
		if(strlen($operations[$i]))
			$query .= $operations[$i]."(`$field`) as $field ,";
		else
			$query .= "$field ,";
	}
	$query = rtrim($query, ',');
	$query .= "FROM `$table`";

	$query .= DBManipulatorMySQL::getConditionClause('id', NULL, $params['domain']);

	$query .= " GROUP BY `$group_by`";

	$query .= " ORDER BY `{$params['sortname']}` {$params['sortorder']};";


	$res = $dbConnection->sendQuery($query);

	$j = 0;
	while($row = $dbConnection->fetchArray($res)) {
		if(is_null($row[$group_by])) continue;
		foreach($fields as $i => $field) {
			// NULL conversion based on field type								
			if(strlen($operations[$i]) > 0 && is_null($row[$field])) $row[$field] = "0.00";
			$result[$j][] = $row[$field];
			
			// add line to total 
			if(in_array($schema[$field]['type'], array('boolean', 'integer', 'float'))
			|| (isset($schema[$field]['result_type']) && in_array($schema[$field]['result_type'], array('boolean', 'integer', 'float')))
			) {
				if(!isset($totals[$field])) $totals[$field] = 0;
				switch($schema[$field]['type']) {
					case 'boolean':
						$row[$field] = (bool) intval($row[$field]);
						break;
					case 'integer':
						$row[$field] = intval($row[$field]);
						break;
					case 'float':							
						$row[$field] = floatval($row[$field]);									
						break;										
				}
				
				$totals[$field] += $row[$field];		
			}
			else {
				$totals[$field] = '';		
			}
		}
		++$j;
	}

	load_class('utils/CurrencyFormatter');
	$formatter = new CurrencyFormatter(CURRENCY_FORMAT);

	foreach($fields as $field) {
		if(in_array($schema[$field]['type'], array('boolean', 'integer', 'float'))
		|| (isset($schema[$field]['result_type']) && in_array($schema[$field]['result_type'], array('boolean', 'integer', 'float')))
		) {
			$totals[$field] = $formatter->getCurrencyOutput($totals[$field]);
		}
	}
	$result[] = $totals;
}				
					
// 2) count rows in the result set					
$count_ids = count($result);

// 3) generate json output
$html = '{'."\n";
$html .= '	"page": "1",'."\n";
$html .= '	"total": "1",'."\n";
$html .= '	"records": "'.$count_ids.'",'."\n";
$html .= '	"rows": ['."\n";

for($i = 0; $i < $count_ids; ++$i) {
	$html .= '		{"id":"'.($i+1).'","cell":[';
	foreach($result[$i] as $field_value) {
		$html .= json_encode($field_value).',';
	}
	$html = rtrim($html, ' ,').']},'."\n";
}
$html = rtrim($html, "\n ,");

$html .= "\n".'	]'."\n";
$html .= '}';

header('Content-type: text/html; charset=UTF-8');
print($html);