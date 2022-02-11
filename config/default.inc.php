<?php
/** 
* Main configuration file
* Constants with name starting with QN_ are defined in eq.lib.php
*/
namespace {
    /**
    * Constants defined in this section are mandatory and cannot be modified in customs config.inc.php
    */    
    
    /** 
    * Add configuration in the global namespace: those cannot be changed in cascade config files.
    */
    
    /**
     *
     * Possible values are: 'ORM' and 'JSON' (router.json)
     */
    define('ROUTING_METHOD', 'JSON');

    /**
     *
     * Routing configuration directory
     */
    define('ROUTING_CONFIG_DIR', QN_BASEDIR.'/config/routing');
    
    
    /**
     * Binary type storage mode
     *
     * Possible values: 'DB' (database) and 'FS' (filesystem)
     */
    define('FILE_STORAGE_MODE', 'FS');


    /**
     * Binaries storage directory
     *
     * Note: ensure http service has read/write permissions on this directory    
     */
    define('FILE_STORAGE_DIR', QN_BASEDIR.'/bin');


    /**
    * Default ACL
    *
    * If no ACL is defined (which is the case by default) for an object nor for its class, any user will be granted the permissions set below
    */
    // Note: in order to allow a user to fully create objects, he must be granted QN_R_CREATE and QN_R_WRITE permissions
    // Note: to set several rights at once, use the OR binary operator	
    // define('DEFAULT_RIGHTS', QN_R_CREATE | QN_R_READ | QN_R_WRITE | QN_R_DELETE | QN_R_MANAGE);
    
    
    define('DEFAULT_RIGHTS', QN_R_CREATE | QN_R_READ | QN_R_DELETE | QN_R_WRITE);
    // define('DEFAULT_RIGHTS', 0);


    /**
    * Access control level
    */
    // By default, the control is done at the class level. It means that a user will be granted the same rights for every objects of a given class.
    // However, sometimes we must take the object id under account (for instance, if pages of a web site can have their own permissions)
    define('ACCESS_CONTROL_LEVEL', 'class');	// allowed values are 'class' or 'object'
    
    
    /**
    * Language parameters
    */
    // The language in which the content must be displayed by default (ISO 639-1)
    define('DEFAULT_LANG', 'fr');
    define('GUEST_USER_LANG', 'fr');
    
    
    /*
    * EMAIL related parameters
    */
    define('EMAIL_SMTP_HOST',               'SSL0.PROVIDER.NET');
    define('EMAIL_SMTP_PORT',               '587');
    define('EMAIL_SMTP_ACCOUNT_DISPLAYNAME','Full Name');    
    define('EMAIL_SMTP_ACCOUNT_USERNAME',   'email.as.username@provider.com');
    define('EMAIL_SMTP_ACCOUNT_PASSWORD',   'password');
    define('EMAIL_SMTP_ACCOUNT_EMAIL',	    'email.to.send.from@provider.com');
    define('EMAIL_SMTP_ABUSE_EMAIL',	    'abuse@example.com');

    /**
    * Email spooler directory
    */
    // Note: ensure http service has read/write permissions on this directory
    define('EMAIL_SPOOL_DIR', QN_BASEDIR.'/spool');

    
    /**
    * Database parameters
    * note: most utilities need these parameters. 
    */

    /**
     * Database replication strategy
     *
     * Possible values: 
     * - 'NO': no replication
     * - 'MS' ('master-slave'): 2 servers; write operations are performed on both servers, read operations are performed on the master only
     * - 'MM' ('multi-master'): any number of servers; write operations are performed on all servers, read operations can be performed on any server
     */
    define('DB_REPLICATION', 'MS'); 
        
    define('DB_DBMS',       'MYSQL');       
    define('DB_CHARSET',    'UTF8');        // 'UTF8' in most cases

    define('DB_HOST',       '127.0.0.1');   // IP or fully qualified domain name (ex.: www.example.com)
    define('DB_PORT',       '3306');        // this is the default port for MySQL
    define('DB_USER',       'root');        // this should be changed for security reasons
    define('DB_PASSWORD',   'test');        // this should be changed for security reasons
    define('DB_NAME',       'equal');       // the name of the DB that you've created or plan to use
    
    // additional DB server(s) can be configured as below
    /* 
    define('DB_1_HOST',       '127.0.0.1');  
    define('DB_1_PORT',       '3306');       
    define('DB_1_USER',       'root');       
    define('DB_1_PASSWORD',   'test');       
    define('DB_1_NAME',       'qinoa');      

    define('DB_2_HOST',       '127.0.0.1');  
    define('DB_2_PORT',       '3306');       
    define('DB_2_USER',       'root');       
    define('DB_2_PASSWORD',   'test');       
    define('DB_2_NAME',       'qinoa');      

    [...]
    */


    /**
    * Default Package
    */
    // Package we'll try to access if nothing is specified in the url (typically while accessing root folder)
    define('DEFAULT_PACKAGE', 'core');
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
    // keep in mind that this parameter does not override the PHP 'upload_max_filesize' and 'post_max_size' directives.
    // So it will not be effective if set higher than those.
    // ('upload_max_filesize' and 'post_max_size' are PHP_INI_PERDIR directives and must be defined in php.ini)

    define('UPLOAD_MAX_FILE_SIZE', 64*1024*1024);		// set upload limit to 64MB

    // if FILE_STORE_MODE is set to 'DB', MySQL config requires a consistent max_allowed_packet (ex. max_allowed_packet = 64M)

    /**
    * Locale parameters
    */
    date_default_timezone_set('Europe/Brussels');


    /**
    * Logging
    */
    define('LOGGING_ENABLED', true);
    // note : keep in mind that enabling logging makes I/O operations a little bit longer
    // define('LOGGING_MODE', QN_R_CREATE | QN_R_WRITE | QN_R_DELETE);



    /**
    * Draft & Versioning
    */
    // draft validity in days
    define('DRAFT_VALIDITY', 0);
    // define('DRAFT_VALIDITY', 10);

    define('VERSIONING_ENABLED', true);

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
    
// todo : generate a random key during install process
    define('AUTH_SECRET_KEY', 'my_secret_key');

    // validity duration of the access token, in seconds
    define('AUTH_ACCESS_TOKEN_VALIDITY', 3600*1);       // set access token validity to 1 hour
    define('AUTH_REFRESH_TOKEN_VALIDITY', 3600*24*90);  // set refresh token validity to 90 days

    // limit sending of auth token to HTTPS
    define('AUTH_TOKEN_HTTPS', false);
    // define('AUTH_TOKEN_HTTPS', true);
    
    define('ROOT_APP_URL', 'http://localhost');
}