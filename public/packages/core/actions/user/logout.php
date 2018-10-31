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
* file: packages/core/actions/user/logout.php
*
* Logs a user out.
*
*/

// easyObject index.php is in charge of setting the context
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

// force silent mode (debug output would corrupt json data)
set_silent(true);

// server-side: generate a new session
session_regenerate_id(true);

header('Content-type: application/json; charset=UTF-8');
// client-side: delete session identification cookie and all cookies from current domain
setcookie(session_name(), '');
foreach ($_COOKIE as $name => $value) setcookie($name, null);

echo json_encode(array('result'=>true), JSON_FORCE_OBJECT);