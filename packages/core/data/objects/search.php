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
* file: packages/core/data/objects/search.php
*
* Returns ids of objects matching the given criterias.
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
		'description'	=>	"Returns ids of objects matching the given criterias.",
		'params' 		=>	array(
								'class_name'	=> array(
													'description' => 'Class to look into.',
													'type' => 'string',
													'required'=> true
													),
								'domain'		=> array(
													'description' => 'The domain holds the criteria that results have to match (serie of conjunctions)',
													'type' => 'array',
													'default' => [[]]
													),
								'order'		=> array(
													'description' => 'Column to use for sorting results.',
													'type' => 'string',
													'default' => 'id'
													),
								'sort'		=> array(
													'description' => 'The direction  (i.e. \'asc\' or \'desc\').',
													'type' => 'string',
													'default' => 'desc'
													),
								'start'		=> array(
													'description' => 'The row from which results have to start.',
													'type' => 'integer',
													'default' => 0
													),
								'limit'		=> array(
													'description' => 'The maximum number of results.',
													'type' => 'integer',
													'default' => 0
													),													
								'lang'			=> array(
													'description '=> 'Specific language for multilang field.',
													'type' => 'string',
													'default' => DEFAULT_LANG
													)
							)
	)
);

// get json result
$result = search($params['class_name'], $params['domain'], $params['order'], $params['sort'], $params['start'], $params['limit']);

header('Content-type: application/json; charset=UTF-8');
echo json_encode(array('result' => $result), JSON_FORCE_OBJECT);
