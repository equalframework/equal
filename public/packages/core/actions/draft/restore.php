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
* Restore an object from the values its draft.
*
* @param string $object_class
* @param integer $object_id
*/

// Dispatcher (index.php) is in charge of setting the context and should include easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

// force silent mode (debug output would corrupt json data)
set_silent(true);

// announce script and fetch parameters values
$params = announce(	
	array(	
    'description'	=>	"Restore one or more deleted object(s).",
    'params' 		=>	array(
                        'class_name'	=> array(
                                           'description' => 'Class of the object(s) to restore.',
                                           'type' => 'string', 
                                           'required'=> true
                                           ),
                        'ids'			=> array(
                                           'description' => 'List of ids of the objects to restore.',
                                           'type' => 'array', 
                                           'required'=> true
                                           )
                        )
	)
);

// assign values with the received parameters
$params = get_params(array('object_class'=>null, 'id'=>null));

// look for a draft of the specified object
$ids = search('core\Version', array(array(array('object_class', '=', $params['class_name']), array('object_id', 'in', $params['ids']), array('state', '=', 'draft'))));

if(count($ids)) {
	$values = read('core\Version', $ids, array('serialized_value'));
    foreach($ids ad $oid) {
        // set the object with the values of its draft
        write($params['class_name'], array($params['id']), unserialize(base64_decode($values[$oid]['serialized_value'])));
    }
	// we no longer need the draft, so remove it (permanently)
	remove('core\Version', $ids, true);
}

// send json result
header('Content-type: application/json; charset=UTF-8');
echo json_encode(array('result' => true, 'error_message_ids' => array()), JSON_FORCE_OBJECT);
