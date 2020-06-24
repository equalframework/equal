<?php
namespace config;

/** 
* Constants defined in this section are mandatory but can be modified/re-defined in customs config.inc.php (i.e.: packages/[package_name]/config.inc.php)
*
*/

// flag constant allowing to detect if config has been exported
define('EXPORT_FLAG', true);

/**
* Debugging
*/	
define('DEBUG_MODE', QN_DEBUG_PHP | QN_DEBUG_ORM | QN_DEBUG_SQL | QN_DEBUG_APP);
// define('DEBUG_MODE', 0);

/**
* List of public objects 
*/
// array of classes involved in right management and SPAM protection mechanism
define ("PUBLIC_OBJECTS", serialize (array ('icway\Comment')));

/**
* File transfer parameters
*/
// maximum authorized size for file upload (in octet)
// keep in mind that this parameter does not override the PHP 'upload_max_filesize' directive
// so it can be more restrictive but will not be effective if set higher
// ('upload_max_filesize' and 'post_max_size' are PHP_INI_PERDIR directives and must be defined in php.ini)

define('UPLOAD_MAX_FILE_SIZE', 64*1024*1024);		// set upload limit to 64Mo


/**
* Locale parameters
*/
date_default_timezone_set('Europe/Brussels');


/**
* Logging
*/
// note : keep in mind that enabling logging makes I/O operations a little bit longer
define('LOGGING_MODE', QN_R_CREATE | QN_R_WRITE | QN_R_DELETE);
//define('LOGGING_MODE', false);


/**
* Draft & Versioning
*/
// draft validity in days
define('DRAFT_VALIDITY', 0);
// define('DRAFT_VALIDITY', 10);


/**
* Date formatting
*/
define('DATE_FORMAT', 'd/m/Y');


/**
* Currency formatting
* Mask examples: '£#,##0.00', '#.##0,00€'
*/
define('CURRENCY_FORMAT', '£#,##0.00');


/**
* Default precision for floating point values
*
*/
define('NUMERIC_DECIMAL_PRECISION', 2);


// define('HTTP_REDIRECT_404', '404.html');
// define('HTTP_REDIRECT_500', '500.html');