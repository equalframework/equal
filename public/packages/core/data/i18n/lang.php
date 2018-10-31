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
* file: packages/core/data/objects/i18n.php
*
*/

// Dispatcher (index.php) is in charge of setting the context and should include easyObject library
defined('__QN_LIB') or die(__FILE__.' cannot be executed directly.');
require_once('../qn.api.php');

use easyobject\orm\I18n as I18n;

// force silent mode (debug output would corrupt json data)
set_silent(true);


// announce script and fetch parameters values
$params = announce(
	array(
		'description'	=>	"Retrieves the translation values related to the specified class.",
		'params' 		=>	array(
								'class_name'	=> array(
													'description' => 'Class for which the transltion file is needed.',
													'type' => 'string',
													'required'=> true
													),
								'lang'			=> array(
													'description'=> 'Language for which values are requested (iso639 code expected).',
													'type' => 'string',
													'default' => DEFAULT_LANG
													)
							)
	)
);


$package = get_object_package_name($params['class_name']);
$class = get_object_name($params['class_name']);

$i18n = &I18n::getInstance();
$result = $i18n->loadTranslationFile($params['lang'], $package, $class);

header('Content-type: text/html; charset=UTF-8');
echo json_encode(array('result'=>$result), JSON_FORCE_OBJECT);
