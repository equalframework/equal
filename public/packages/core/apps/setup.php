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
* file: packages/core/apps/setup.php
*
* Checks the current installation.
*
*/

// Dispatcher (index.php) is in charge of setting the context and should include easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');


// todo : this script should also test the php configuration and the folders permissions



QNlib::load_class('utils/HtmlWrapper');

// we instanciate eventListener to catch all errors (we need to do it here since we might not use the obect Manager)
new eventListener();
set_silent(true);

$dbConnection = &DBConnection::getInstance(DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD, DB_DBMS);


// result of the tests : array containing errors (when no errors are found, array is empty)
$result = array();

// A) DATABASE ACCESS

// 1) test access to DB server
if(!DBManipulator::is_db_server(DB_HOST,DB_PORT)) $result[] = "Unable to find a ".DB_DBMS." server at specified location (".DB_HOST.":".DB_PORT.")";
else {
	// 2) try to connect to DB server
	if(!$dbConnection->connect(false)) $result[] = "Unable to establish a connection to specified ".DB_DBMS." server (".DB_HOST.":".DB_PORT.")";
	else {
		// 3) try to select specified DB
		if(!$dbConnection->select(DB_NAME)) $result[] = "Database specified in config (".DB_NAME.") not found";
		$dbConnection->disconnect();
	}
}

// B) FILESYSTEM ACCESS

if(FILE_STORAGE_MODE == 'FS') {
    // array holding folders to be tested
    $folders = array(FILE_STORAGE_DIR);
    // if ( posix_getuid() == fileowner($file_name) )
    foreach($folders as $folder) {
        if(!file_exists($folder) || !is_writable($folder)) $result[] = "PHP process has no write access on folder $folder";
    }

    // todo: check 777 mod for folder library/classes/utils/HTMLPurifier/HTMLPurifier/DefinitionCache
}


// Output result

$html = new HtmlWrapper();

if(!count($result)) {
	$html->add("<b>Congratulations, your installation is ready to be used!</b><br /><br />\n");
	$html->add("Now, you might want to:\n");
	$html->add($ul = new HtmlBlock(0, 'ul'));
	$ul->add("<li><a href=\"index.php?show=core_user_login\">Login</a> with a specific account</li>\n");
	$ul->add("<li>Run some <a href=\"index.php?show=core_utils\">utilities</a></li>\n");
	$ul->add("<li>Start using the <a href=\"index.php?show=core_manage\">manager</a></li>\n");
}
else {
	$html->add($pre = new HtmlBlock(0, 'pre'));
	$pre->add("<b>Some errors have been detected in your installation:</b>\n");
	$pre->add($ul = new HtmlBlock(0, 'ul'));
	foreach($result as $message) {
		$ul->add("<li>".$message."</li>\n");
	}
}

print($html);
