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

/** 
* Add stuff in the global namespace.
* Constants defined in this file are mandatory and cannot be modified in customs config.inc.php
*/
namespace {
    /**
    *	All constants required by the core are prefixed with QN_
    *	(in addition, user might define its own constants following his own formatting rules)
    */    
    
    /**
    * Current version of Qinoa
    */
    define('QN_VERSION', '1.0.0');
    
    /**
    * Root directory of current install (current file is expected to be located in /config)
    */ 
    define('QN_BASEDIR', realpath(dirname(__FILE__).'/..'));
    
	/**
	* Error codes
    * we use negative values to make it possible to distinguish error codes from object ids
	*/
	define('UNKNOWN_ERROR',		 -1);	// something went wrong (that requires to check the logs)
	define('MISSING_PARAM',		 -2);	// one or more mandatory parameters are missing
	define('INVALID_PARAM',		 -4);	// one or more parameters have invalid or incompatible value
	define('SQL_ERROR',			 -8);	// error while building SQL query or processing it (check that object class matches DB schema)
	define('UNKNOWN_OBJECT',	 -16);	// unknown resource (class, object, view, ...)
	define('NOT_ALLOWED',		 -32);	// action violates some rule (including UPLOAD_MAX_FILE_SIZE for binary fields) or user don't have required permissions

    
        
   	define('QN_ERROR_UNKNOWN',	        UNKNOWN_ERROR);
	define('QN_ERROR_MISSING_PARAM',    MISSING_PARAM);	    
	define('QN_ERROR_INVALID_PARAM',	INVALID_PARAM);	    
	define('QN_ERROR_SQL',			    SQL_ERROR);	        
	define('QN_ERROR_UNKNOWN_OBJECT',	UNKNOWN_OBJECT);	
	define('QN_ERROR_NOT_ALLOWED',		NOT_ALLOWED);    
	define('QN_ERROR_LOCKED_OBJECT',    -64);    
	define('QN_ERROR_CONFLICT_OBJECT',  -128);
    define('QN_ERROR_INVALID_USER',     -256);              // auth failure
	define('QN_ERROR_UNKNOWN_SERVICE',  -512);              // server errror : missing service
    define('QN_ERROR_INVALID_CONFIG',   -1024);             // serfver error : faulty configuration
    
    

/*    
function error_message($error_code) {
    switch($error_code) {
    case QN_ERROR_UNKNOWN:          return 'unknown error';
    case QN_ERROR_MISSING_PARAM:    return 'a mandatory parameter is missing';    
    case QN_ERROR_INVALID_PARAM:    return 'a provided parameter is invalid';
    case QN_ERROR_SQL:              return 'an SQL error occured';
    case QN_ERROR_UNKNOWN_OBJECT:   return 'specified object is unknown';
    case QN_ERROR_NOT_ALLOWED:      return 'requested action not allowed';
    }
}
*/

function qn_error_name($error_id) {
    switch($error_id) {
    case QN_ERROR_MISSING_PARAM:    return 'MISSING_PARAM';
    case QN_ERROR_INVALID_PARAM:    return 'INVALID_PARAM';
    case QN_ERROR_SQL:              return 'SQL_ERROR';    
    case QN_ERROR_NOT_ALLOWED:      return 'NOT_ALLOWED';
    case QN_ERROR_UNKNOWN_OBJECT:	return 'UNKNOWN_OBJECT';
    case QN_ERROR_INVALID_CONFIG:   return 'INVALID_CONFIG';
    case QN_ERROR_UNKNOWN_SERVICE:  return 'UNKNOWN_SERVICE';
    case QN_ERROR_LOCKED_OBJECT:    return 'LOCKED_OBJECT';
    case QN_ERROR_CONFLICT_OBJECT:  return 'CONFLICT_OBJECT';
    case QN_ERROR_INVALID_USER:     return 'INVALID_CREDENTIALS';    
    }
    return 'UNKNOWN_ERROR';
}

function qn_error_code($error_name) {
    switch($error_name) {
    case 'MISSING_PARAM':       return QN_ERROR_MISSING_PARAM;
    case 'INVALID_PARAM':       return QN_ERROR_INVALID_PARAM;
    case 'SQL_ERROR':           return QN_ERROR_SQL;
    case 'NOT_ALLOWED':         return QN_ERROR_NOT_ALLOWED;
    case 'UNKNOWN_OBJECT':	    return QN_ERROR_UNKNOWN_OBJECT;
    case 'INVALID_CONFIG':      return QN_ERROR_INVALID_CONFIG;
    case 'UNKNOWN_SERVICE':     return QN_ERROR_UNKNOWN_SERVICE;
    case 'LOCKED_OBJECT':       return QN_ERROR_LOCKED_OBJECT;
    case 'CONFLICT_OBJECT':     return QN_ERROR_CONFLICT_OBJECT;
    case 'INVALID_CREDENTIALS': return QN_ERROR_INVALID_USER;    
    }
    return QN_ERROR_UNKNOWN;
}

/*

        '400' => 'Bad Request',				missing data or invalid format for mandatory parameter
        '401' => 'Unauthorized',			auth required or auth failure
        '403' => 'Forbidden',				user has not enough privilege to perform requested operation
        '404' => 'Not Found',				route does not exist
        '405' => 'Method Not Allowed',		route exists but no controller is assigned for given HTTP method
        '406' => 'Not Acceptable',			unrecognized payload format
        '409' => 'Conflict',				version conflict
        '423' => 'Locked',				    resource is currently locked
        '429' => 'Too Many Requests',		request blocked because client reached the maximum allowed requests quota
        '456' => 'Unrecoverable Error',		an unhandled scenario happend and operation could not be performed
        
        // server error (inside controller code)
        '500' => 'Internal Server Error'    something went wrong (details should be available in the log)
        '503' => 'Service Unavailable',     a required service is unavailable

*/
function qn_error_http($error_id) {
    switch($error_id) {
    case 0:                         return 200;        
    case QN_ERROR_MISSING_PARAM:    return 400;
    case QN_ERROR_INVALID_PARAM:    return 400;
    case QN_ERROR_SQL:              return 456;    
    case QN_ERROR_NOT_ALLOWED:      return 403;
    case QN_ERROR_UNKNOWN_OBJECT:	return 404;
    case QN_ERROR_LOCKED_OBJECT:    return 423;
    case QN_ERROR_CONFLICT_OBJECT:  return 409;
    case QN_ERROR_INVALID_USER:     return 401;
    // server errors
    case QN_ERROR_UNKNOWN:
    case QN_ERROR_INVALID_CONFIG:   return 500;    
    case QN_ERROR_UNKNOWN_SERVICE:  return 503;
    }
    // fallback to 'Internal Server Error'
    return 500;
}
    
	/**
	* Debugging modes
	*/	
	define('DEBUG_PHP',			1);
	define('DEBUG_SQL',			2);
	define('DEBUG_ORM',			4);
	define('DEBUG_APP',			8);

	define('QN_DEBUG_PHP',			1);
	define('QN_DEBUG_SQL',			2);
	define('QN_DEBUG_ORM',			4);
	define('QN_DEBUG_APP',			8);    
    
    define('QN_REPORT_DEBUG',       E_USER_NOTICE);     // 1024
    define('QN_REPORT_WARNING',     E_USER_WARNING);    // 512  
    define('QN_REPORT_ERROR',       E_USER_ERROR);      // 256
    define('QN_REPORT_FATAL',       E_ERROR);           // 1    
    
    /**
    * Logs storage directory
    */
    // Note: ensure http service has read/write permissions on this directory
    define('LOG_STORAGE_DIR', QN_BASEDIR.'/log');
    define('QN_LOG_STORAGE_DIR', QN_BASEDIR.'/log');    

    // EventHandler will deal with error and debug messages depending on debug source value
    ini_set('display_errors', 0);
    ini_set('html_errors', false);    
    ini_set('error_log', QN_LOG_STORAGE_DIR.'/error.log');
    
    // use QN_REPORT_x, E_ERROR for fatal errors only, E_ALL for all errors
    error_reporting(E_ALL);

    
	/**
	* Users & Groups permissions masks
	*/
	define('R_CREATE',			1);
	define('R_READ',			2);
	define('R_WRITE',			4);
	define('R_DELETE',			8);
	define('R_MANAGE',			16);

	/**
	* Built-in Users and Groups
	*
	* Note : make sure that the ids in DB are set and matching these
	*/
	define('GUEST_USER_ID',		0);
	define('ROOT_USER_ID',		1);
    
	define('DEFAULT_GROUP_ID',	1);	// default group (all users are members of the default group)
    
    /**
    * Session parameters
    */
    // Use session identification by COOKIE only
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    // and make sure not to rewrite URL
    ini_set('session.use_trans_sid', '0');
    ini_set('url_rewriter.tags', '');


    /**
    *
    * Possible values are: 'ORM' and 'JSON' (router.json)
    */
    define('ROUTING_METHOD', 'JSON');
    
    /**
    * Binary type storage mode
    *
    * Possible values are: 'DB' (database) and 'FS' (filesystem)
    */
    define('FILE_STORAGE_MODE', 'FS');


    /**
    * Binaries storage directory
    */
    // Note: ensure http service has read/write permissions on this directory
    define('FILE_STORAGE_DIR', QN_BASEDIR.'/bin');


    /**
    * Default ACL
    *
    * If no ACL is defined (which is the case by default) for an object nor for its class, any user will be granted the permissions set below
    */
    // Note: in order to allow a user to fully create objects, he must be granted R_CREATE and R_WRITE permissions
    // Note: to set several rights at once, use the OR binary operator	
    // define('DEFAULT_RIGHTS', R_CREATE | R_READ | R_WRITE | R_DELETE | R_MANAGE);
    
    
    define('DEFAULT_RIGHTS', R_CREATE | R_READ |R_DELETE | R_WRITE);
    // define('DEFAULT_RIGHTS', 0);


    /**
    * Access control level
    */
    // By default, the control is done at the class level. It means that a user will be granted the same rights for every objects of a given class.
    // However, sometimes we must take the object id under account (for instance, if pages of a web site can have their own permissions)
    define('CONTROL_LEVEL', 'class');	// allowed values are 'class' or ' object'
    
    
    /**
    * Language parameters
    */
    // The language in which the content must be displayed by default (ISO 639-1)
    define('DEFAULT_LANG', 'fr');
    define('GUEST_USER_LANG', 'fr');
    
    
    /*
    * EMAIL related parameters
    */
    define('EMAIL_SMTP_HOST',               'smtp.gmail.com');
    define('EMAIL_SMTP_PORT',               '465');
    define('EMAIL_SMTP_ACCOUNT_USERNAME',   'info@example.com');
    define('EMAIL_SMTP_ACCOUNT_PASSWORD',   'examplepassword');
    define('EMAIL_SMTP_ACCOUNT_EMAIL',	    'info@example.com');    

    /**
    * Email spooler directory
    */
    // Note: ensure http service has read/write permissions on this directory
    define('EMAIL_SPOOL_DIR', QN_BASEDIR.'/spool');
        
    /**
    * Database parameters
    * note: most utilities need these parameters. 
    */
    define('DB_DBMS',		'MYSQL');		// only MySQL is supported so far
    define('DB_HOST',		'localhost');   // the full qualified domain name (ex.: www.example.com)
    define('DB_PORT',		'3306');		// this is the default port for MySQL
    define('DB_USER',		'root');        // this should be changed for security reasons
    define('DB_PASSWORD',	'');			// this should be changed for security reasons
    define('DB_NAME', 		'my_db');	    // specify the name of the DB that you have created or you plan to use
    define('DB_CHARSET',	'UTF8');		// unless you are really sure of what you're doing, leave this constant to 'UTF8'

    /**
    * Default Package
    */
    // Package we'll try to access if nothing is specified in the url (typically while accessing root folder)
    define('DEFAULT_PACKAGE', 'resiway');    
}
namespace config {
    /** 
    * Constants defined in this section are mandatory but can be modified/re-defined in customs config.inc.php (i.e.: packages/[package_name]/config.inc.php)
    *
    */

    // flag constant allowing to detect if config has been exported
    define('EXPORT_FLAG', true);
    
    /**
    * Debugging
    */	
    define('DEBUG_MODE', DEBUG_PHP | DEBUG_ORM | DEBUG_SQL | DEBUG_APP);
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
    define('LOGGING_MODE', R_CREATE | R_WRITE | R_DELETE);
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


// todo : generate a random key during install process
    define('AUTH_SECRET_KEY', 'my_secret_key');
}