# Configuration

eQual allows to define globally some values that are used across the different services as configuration properties.  

These properties are defined through config files. 

eQual supports cascading configuration : each package can have a config file of its own that defines specific properties or overwrites existing properties.  

Config files are optional, and the default values for mandatory properties are listed in the configuration schema (see below). 

## schema.json

A `schema.json` file is located under the `./config/` folder. That file holds a comprehensive description of the role and usage of each available constant. This file is not meant to be modified. Instead, constants that must be modified according to the current environment can be set in a `config.json` file.

## config.json

Customization can be achieved by using a custom `config.json` file that must be placed under : `./config/` along with the `schema.json`.  

* **`config.json`**, to configure your database and other parameters.

* **`config-example.json`**, rename it as `config.inc.php` to use custom configuration.

Properties are exported as (global) constants. Services, controllers and classes are responsible of announcing which constants they expect (through a `getConstants()` method). If one of the required constant is missing, an exception is raised.


Settings values defined in config files must always be called by using the `constant()` function. This is because, since they're not defined at the moment the parsing is done, using literal notation would result in warnings and wrong values assignments.

Some constants might be required by mandatory services. Those must therefore be defined immediately when the config file is read. Those properties are marked as 'instant'.  When no value is given in `config.json` for such property, if a 'default' or an 'environment' property is set in the `schema.json`, the constant is declared using the retrieved value, if not, the property is ignored and no constant is defined, unless the property is also marked as 'required' in which case an Exception is raised.

The list below shows the details for supported constants and their roles.

### General properties

| **CONSTANT**         | **DEFAULT VALUE**                                     | **DESCRIPTION**                                                                                                                                 |
| -------------------- | ----------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- |
| ROUTING_METHOD       | JSON                                                  | Possible values are: 'ORM' and 'JSON' (router.json).                                                                                            |
| ROUTING_CONFIG_DIR   | EQ_BASEDIR.'/config/routing'                          | Routing configuration directory.                                                                                                                |
| FILE_STORAGE_MODE    | FS                                                    | Binary type storage mode (Possible values: 'DB' (database) and 'FS' (filesystem)).                                                              |
| FILE_STORAGE_DIR     | EQ_BASEDIR.'/bin'                                     | Binaries storage directory.                                                                                                                     |
| DEFAULT_RIGHTS       | EQ_R_CREATE \| EQ_R_READ \| EQ_R_DELETE \| EQ_R_WRITE | If no ACL is defined (which is the case by default) for an object nor for its class, any user will be granted the permissions in this constant. |
| ACCESS_CONTROL_LEVEL | class                                                 | By default, the control is done at the class level. It means that a user will be granted the same rights for every objects of a given class.    |
| DEFAULT_LANG         | en                                                    | The language in which the content must be displayed by default (ISO 639-1).                                                                     |
| GUEST_USER_LANG      | en                                                    | The language in which the content must be displayed by default (ISO 639-1) for the Guest User.                                                  |
| DEFAULT_PACKAGE      | core                                                  | Package we'll try to access if nothing is specified in the url (typically while accessing root folder).                                         |

### Email properties

| **CONSTANT**                   | **DEFAULT VALUE**               | **DESCRIPTION**                                 |
| ------------------------------ | ------------------------------- | ----------------------------------------------- |
| EMAIL_SMTP_HOST                | SSL0.PROVIDER.NET               | Email host.                                     |
| EMAIL_SMTP_PORT                | 587                             | Email port.                                     |
| EMAIL_SMTP_ACCOUNT_DISPLAYNAME | Full Name                       | Email display name.                             |
| EMAIL_SMTP_ACCOUNT_USERNAME    | email.as.username@provider.com  | Email username.                                 |
| EMAIL_SMTP_ACCOUNT_PASSWORD    | password                        | Email password.                                 |
| EMAIL_SMTP_ACCOUNT_EMAIL       | email.to.send.from@provider.com | Email address the email is sent from.           |
| EMAIL_SMTP_ABUSE_EMAIL         | abuse@example.com               | Email address to handle abusive emails (spams). |
| EMAIL_SPOOL_DIR                | EQ_BASEDIR.'/spool'             | Email spooler directory.                        |

### Database related properties

| **CONSTANT**   | **DEFAULT VALUE**                                      | **DESCRIPTION**                                                                                                                                                                                                                                                                                                                                                    |
| -------------- | ------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| DB_REPLICATION | MS                                                     | Database replication : <br />* **'NO'**: no replication <br />\*  **'MS'** ('master-slave'): 2 servers; write operations are performed on both servers, read operations are performed on the master only <br />\* **'MM'** ('multi-master'): any number of servers; write operations are performed on all servers, read operations can be performed on any server. |
| DB_DBMS        | MYSQL                                                  | Selection of the Database Management System.                                                                                                                                                                                                                                                                                                                       |
| DB_CHARSET     | UTF8                                                   | Default Charset.                                                                                                                                                                                                                                                                                                                                                   |
| DB_HOST        | getenv('EQ_DB_HOST')?getenv('EQ_DB_HOST'):'127.0.0.1') | DB host.                                                                                                                                                                                                                                                                                                                                                           |
| DB_PORT        | getenv('EQ_DB_PORT')?getenv('EQ_DB_PORT'):'3306')      | DB port.                                                                                                                                                                                                                                                                                                                                                           |
| DB_USER        | getenv('EQ_DB_USER')?getenv('EQ_DB_USER'):'root')      | DB user.                                                                                                                                                                                                                                                                                                                                                           |
| DB_PASSWORD    | getenv('EQ_DB_PASS')?getenv('EQ_DB_PASS'):'test')      | DB password.                                                                                                                                                                                                                                                                                                                                                       |
| DB_NAME        | getenv('EQ_DB_NAME')?getenv('EQ_DB_NAME'):'equal')     | DB name.                                                                                                                                                                                                                                                                                                                                                           |



### Other properies

| **CONSTANT**                | **DEFAULT VALUE**                                            | **DESCRIPTION**                                                            |
| --------------------------- | ------------------------------------------------------------ | -------------------------------------------------------------------------- |
| DEBUG_MODE                  | EQ_DEBUG_PHP \| EQ_DEBUG_ORM \| EQ_DEBUG_SQL \| EQ_DEBUG_APP | Filter the kind of errors that are present in the console.                 |
| UPLOAD_MAX_FILE_SIZE        | 64\*1024*1024                                                | maximum authorized size for file upload (in octet).                        |
| LOGGING_ENABLED             | true                                                         | Enable/Disable the logs (to keep track of object changes).                 |
| DRAFT_VALIDITY              | 0                                                            | Draft validity in days.                                                    |
| VERSIONING_ENABLED          | true                                                         | Enable/Disable versions of object changes.                                 |
| AUTH_SECRET_KEY             | my_secret_key                                                | Random key generated during install process.                               |
| AUTH_ACCESS_TOKEN_VALIDITY  | 3600*1                                                       | Validity duration of the access token, in seconds.                         |
| AUTH_REFRESH_TOKEN_VALIDITY | 3600\*24*90                                                  | Refresh token validity, in days.                                           |
| AUTH_TOKEN_HTTPS            | false                                                        | Limit sending of auth token to HTTPS.                                      |
| APP_URL                     | http://equal.local                                           | Root URL of the application.                                               |
| APP_NAME                    | eQual                                                        | Custom name of the Application.                                            |
| APP_LOGO_URL                | /_assets/img/logo.svg                                        | Absolute URL of the logo to be used for the App (under the public folder). |
| ORG_NAME                    | eQual framework                                              | Name or brand of the organization that owns the instance.                  |
| ORG_URL                     | https://equal.run                                            | Official website of the organization.                                      |



!!! note "About integer values"  
    Properties holding an integer value can be given as a string that supports the following shorthand notations (case-sensitive):

    * memory notation (converted in bytes)
        * KB (for KiloBytes)
        * MB (for MegaBytes) 
        * GB (for GigaBytes)
    
    * time notations (converted in seconds)
        * s for seconds (default)
        * m for minutes (=60s)
        * h for hours (=60m)
        * d for days (=24h)
        * w for weeks (=7d)
        * M for months (=4w)
        * Y for years (=12m)

---

## UI configuration 

In order to bootstrap communication with the back-end, the front-end needs some information. To that end, a configuration file is expected under `./public/_assets/env/config.json` to hold a series of public details that are needed by the UI.

That file can be generated manually or using the `init` controller.

The properties, roles and default values are describes in the list below.

| name                      | default value                                 | origin                                |
| ------------------------- | --------------------------------------------- | ------------------------------------- |
| production                | true                                          | ENV_MODE                              |
| parent_domain             | equal.local                                   | parse_url(ROOT_APP_URL, PHP_URL_HOST) |
| backend_url               | http://equal.local                            | ROOT_APP_URL                          |
| rest_api_url              | http://equal.local/                           | ROOT_APP_URL.'/'                      |
| lang                      | en                                            | DEFAULT_LANG                          |
| locale                    | en                                            | L10N_LOCALE                           |
| version                   | 1.0 (2022)                                    | EQ_VERSION                            |
| company_name              | eQual framework                               | ORG_NAME                              |
| company_url               | https://equal.run/                            | ORG_URL                               |
| app_name                  | eQual                                         | APP_NAME                              |
| app_logo_url              | /_assets/img/logo.svg                         | APP_LOGO_URL                          |
| app_settings_root_package | core                                          | (deprecated?)                         |
| license                   | AGPL v3.0                                     | (manual - must be compliant)          |
| license_url               | https://www.gnu.org/licenses/agpl-3.0.en.html | (manual - must be compliant)          |

---

