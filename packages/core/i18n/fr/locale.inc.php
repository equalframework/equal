<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
/**
* Use the 'config' namespace to use config\define() instead of define()
*/
namespace config;


define('QN_DATE_FIRST_DAY_OF_WEEK', 		2);						// 1=>Sunday,7=>Saterday
define('QN_DATE_FORMAT',					'd/m/Y');
define('QN_TIME_FORMAT',					'H:i:s');
define('QN_DATETIME_FORMAT',				'd/m/Y H:i:s');
define('QN_NUMERIC_THOUSANDS_SEPARATOR',	'.');
define('QN_NUMERIC_DECIMAL_POINT',			',');
define('QN_NUMERIC_DECIMAL_PRECISION',		2);
define('QN_CURRENCY_SYMBOL',				'€');
define('QN_CURRENCY_SYMBOL_INT',			'EUR');
define('QN_CURRENCY_FORMAT',				'#.##0,00€');