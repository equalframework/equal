<?php
/**
*	This file is part of the easyObject project.
*	http://www.cedricfrancoys.be/easyobject
*
*	Copyright (C) 2012  Cedric Francoys
*
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
* Save a draft for an object.
*
* @param string $object_class
* @param integer $object_id
* @param array $values
*/

// Dispatcher (index.php) is in charge of setting the context and should include easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

// force silent mode (debug output would corrupt json data)
set_silent(true);

// ensure required parameters have been transmitted
// assign values with the received parameters
$params = get_params(array('object_class'=>null, 'id'=>null));

$object_id = $params['id'];
$object_class = $params['class_name'];
$values = $_REQUEST;

// draft must be related to an existing object id
($object_id > 0) or exit;

// obtain the object instance
$object = &get($object_class, $object_id);
// create an array containing the object fields ($values may contain data we don't need)
$object_fields_array = array();
foreach($object->getColumns() as $field => $def) if(isset($values[$field])) $object_fields_array[$field] = $values[$field];

// remove (permanently) previous draft if any
remove('core\Version', search('core\Version', array(array(array('object_class', '=', $object_class), array('object_id', '=', $object_id), array('state', '=', 'draft')))), true);
// create a new draft with given values
$result = update('core\Version', array(0), array('created'			=> date("Y-m-d H:i:s"),
												'creator'			=> user_id(),
												'object_class'		=> $object_class,
												'object_id'			=> $object_id,
												'state'				=> 'draft',
												'serialized_value'	=> base64_encode(serialize($object_fields_array))));
                                                
// send json result
header('Content-type: application/json; charset=UTF-8');
echo json_encode(array('result' => true, 'error_message_ids' => array()), JSON_FORCE_OBJECT);