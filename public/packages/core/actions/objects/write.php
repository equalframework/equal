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
* file: packages/core/actions/objects/write.php
*
* Update specified object(s) with given values.
°
*/

// Dispatcher (index.php) is in charge of setting the context and should include easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

// force silent mode (debug output would corrupt json data)
set_silent(true);

// announce script and fetch parameters values
$params = announce(	
	array(	
		'description'	=>	"Update specified object(s) according to submitted values.",
		'params' 		=>	array(
								'class_name'	=> array(
													'description' => 'Class of the objets to update.',
													'type' => 'string', 
													'required'=> true
													),
								'ids'			=> array(
													'description' => 'List of ids of the objects to browse.',
													'type' => 'array', 
													'required'=> true
													),
								'public_code'	=> array(
													'description' => 'Code for updating public objects (must be equal to SESSION_ID).',
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

// additional check for public objects (i.e. that can be creatd or modified by guest users)
if(in_array($params['class_name'], unserialize(PUBLIC_OBJECTS))) {
	// this is a public object, so we check if public_code is given and if it matches the current session id
	if(is_null($params['public_code']) || $params['public_code'] != session_id()) die();
}

// first we try to validate the submitted content
$error_message_ids = array();
$validation = validate($params['class_name'], $_REQUEST);

// if something went wrong during the validation, abort the process
if($validation === false) $result = UNKNOWN_ERROR;
else {
	if(count($validation)) {
		// one or more fields have invalid value
		$error_message_ids = $validation;
		$result = INVALID_PARAM;
	}
	else {
		// values are valid : update object and get json result
		$result = write($params['class_name'], $params['ids'], $_REQUEST, $params['lang']);
		// look for deprecated draft
		$ids = search('core\Version', array(array(array('object_class', '=', $params['class_name']), array('object_id', '=', $params['ids']), array('state', '=', 'draft'))));
		// if update went well, remove permanently pending draft, if any
		if($result === true && !empty($ids)) remove('core\Version', $ids, true);
	}
}

// note: in case of success, $result will contain an array (with ids of newly created objects, if any)
// otherwise it will hold an error code (integer)

// send json result
header('Content-type: application/json; charset=UTF-8');
echo json_encode(array('result' => $result, 'error_message_ids' => $error_message_ids), JSON_FORCE_OBJECT);
