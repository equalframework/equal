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
* file: packages/core/data/i18n/locale.php
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
	'description'	=>	"Retrieves the configuration values related to the specified locale.",
	'params' 		=>	array(
						'locale'	=>	array(
										'description'	=> 'Language for which values are requested (iso639 code expected).',
										'type'			=> 'string',
										'default'		=> DEFAULT_LANG
										)
						)
	)
);


// try to load specified locale (retrieve parameters set in the locale.inc.php script)
load_class('orm/I18n');
$i18n = &I18n::getInstance();
$result = I18n::getLocale($params['locale']);
if($result != UNKNOWN_OBJECT) {
	foreach($result as $name => $value) {
		// convert PHP datetime notation to javascript (dateJS)	
		if(in_array($name, array('QN_DATE_FORMAT', 'QN_TIME_FORMAT', 'QN_DATETIME_FORMAT'))) {
			$result[$name] = str_replace(array('d', 'm', 'Y', 'H', 'i', 's'), array('dd', 'mm', 'yy', 'hh', 'mm', 'ss'), $value);
		}
	}
}


header('Content-type: application/json; charset=UTF-8');
echo json_encode(array('result'=>$result), JSON_FORCE_OBJECT);