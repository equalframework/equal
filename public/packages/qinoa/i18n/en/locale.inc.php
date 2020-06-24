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

*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
/** 
* Use the 'config' namespace to use config\define() instead of define()
*/
namespace config;


define('QN_DATE_FIRST_DAY_OF_WEEK', 		2);						// 1=>Sunday,7=>Saterday
define('QN_DATE_FORMAT',					'd/m/Y');
define('QN_TIME_FORMAT',					'H:i:s');
define('QN_DATETIME_FORMAT',				'd/m/Y H:i:s');
define('QN_NUMERIC_THOUSANDS_SEPARATOR',	',');
define('QN_NUMERIC_DECIMAL_POINT',			'.');
define('QN_NUMERIC_DECIMAL_PRECISION',		2);
define('QN_CURRENCY_SYMBOL',				'£');
define('QN_CURRENCY_SYMBOL_INT',			'GBP');
define('QN_CURRENCY_FORMAT',				'£#,##0.00');