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
* file: packages/core/data/objects/browse.php
*
* Returns the values of the specified fields for the objects pointed by the given ids.
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
    'description'	=>	"Returns the values of the specified fields for the given objects ids.",
    'params' 		=>	array(
                        'class_name'	=>  array(
                                            'description' => 'Class to look into.',
                                            'type' => 'string', 
                                            'required'=> true
                                            ),
                        'ids'			=>  array(
                                            'description' => 'List of ids of the objects to browse.',
                                            'type' => 'array'
                                            ),
                        'fields'		=>  array(
                                            'description' => 'Wanted fields. If not specified, all simple fields are returned.',
                                            'type' => 'array', 
                                            'default' => null
                                            ),
                        'lang'			=>  array(
                                            'description'=> 'Specific language for multilang field.',
                                            'type' => 'string', 
                                            'default' => DEFAULT_LANG
                                            )
                        )
	)
);

// get resulting array
$result = read($params['class_name'], $params['ids'], $params['fields'], $params['lang']);

// output json result
header('Content-type: application/json; charset=UTF-8');
echo json_encode(array('result' => $result), JSON_FORCE_OBJECT);