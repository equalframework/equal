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
* file: packages/core/actions/user/login.php
*
* Logs a user in.
*
* @param string $login
* @param string $password (locked MD5 value)
*/

// Dispatcher (index.php) is in charge of setting the context and should include easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

// force silent mode (debug output would corrupt json data)
set_silent(true);

// announce script and fetch parameters values
$params = announce(
	array(
		'description'	=>	"Attempt to log a user in.",
		'params' 		=>	array(
							'login'		=>	array(
											'description' => 'Login of the user.',
											'type' => 'string',
											'required'=> true
											),
							'password'	=>	array(
											'description' => 'Locked md5 value of user\'s password.',
											'type' => 'string',
											'required'=> true
											)
							)
	)
);



// first we try to validate the submitted credentials
$error_message_ids = array();
$validation = validate('core\User', $params);

// if something went wrong during the validation, abort the login process
if($validation === false) $result = UNKNOWN_ERROR;
else {
	if(count($validation)) {
		// one or more fields have invalid value
		$error_message_ids = $validation;
		$result = INVALID_PARAM;
	}
	else {
		// values are valid : try to log in
        $result = login($params['login'], $params['password']);
	}
}

// send json result
header('Content-type: application/json; charset=UTF-8');
// todo : on success, we should rather return the user object (with all simple fields)
echo json_encode(array('result' => $result, 'url' => 'index.php?do=core_user_start', 'error_message_ids' => $error_message_ids), JSON_FORCE_OBJECT);