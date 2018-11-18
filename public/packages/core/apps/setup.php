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
 @actions   this is a data provider: no change is made to the stored data
 @rights    everyone has read access on these data
 @returns   list of objects matching given criteria
*/

// announce script and fetch parameters values
list($params, $providers) = eQual::announce(
	[	
        'description'	=>	"Checks the validity of current installation.",
        'params' 		=>	[],
        'providers'		=>	['orm'],
        'constants'     =>  ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_DBMS']
	]
);

list($orm) = [ $providers['orm'] ];


// todo : this script should also test the php configuration and the folders permissions

$db = $orm->getDB();


if(!$db->canConnect()) {
    //$result[] = "Unable to find a ".DB_DBMS." server at specified location (".DB_HOST.":".DB_PORT.")";
echo  "Unable to find a ".DB_DBMS." server at specified location (".DB_HOST.":".DB_PORT.")";
}
else {
    echo "connection ok";
}



// result of the tests : array containing errors (when no errors are found, array is empty)
$result = array();

// A) DATABASE ACCESS

// 1) test access to DB server
if(!$db->canConnect()) {
    $result[] = "Unable to find a ".DB_DBMS." server at specified location (".DB_HOST.":".DB_PORT.")";
}

else {
	// 2) try to connect to DB server
	if(!$db->connect(false)) $result[] = "Unable to establish a connection to specified ".DB_DBMS." server (".DB_HOST.":".DB_PORT.")";
	else {
		// 3) try to select specified DB
		if(!$db->select(DB_NAME)) $result[] = "Database specified in config (".DB_NAME.") not found";
		$db->disconnect();
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

    // todo: check permissions mod for folders
}


// Output result

print_r($result);