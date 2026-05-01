## Installation & Config utilities

## Cascading Configuration

eQual supports cascading configuration, where constants defined in the main `config/config.json` file can be overridden by package-specific configuration files.

### How It Works:
1. The system first loads constants from the global `config/config.json` file.
2. If a package-specific `config.json` file exists, its constants override the global ones.
3. Overridden constants are limited to the package they belong to.

This approach allows for flexible and context-specific configuration management.

### File Permissions

Ensure that the `/var/www/` directory and its contents have `www-data:www-data` as the owner.

You can verify mandatory folders and file permissions using the following command:
```bash
$ ./equal.run --do=test_fs-consistency
```

#### `db-connectivity`

| **PATH**        | `core\actions\test\db-connectivity.php`                                                       |
| --------------- | --------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_db-connectivity`                                                                    |
| **CLI**         | `$ ./equal.run --do=test_db-connectivity`                                                     |
| **DESCRIPTION** | Tests connectivity to the DBMS server (check if we're able to establish a TCP/IP connection). |

#### `db-access`

| **PATH**        | `core\actions\test\db-access.php`                                                                                                     |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_db-access`                                                                                                                  |
| **CLI**         | `$ ./equal.run --do=test_db-access`                                                                                                   |
| **DESCRIPTION** | Tests access to the database specified in the config file. This controller uses db-connectivity before trying to access the database. |

#### `fs-consistency`

| **PATH**        | `core\actions\test\fs-consistency.php`                                                                                                                                                          |
| --------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=test_fs-consistency`                                                                                                                                                                       |
| **CLI**         | `$ ./equal.run --do=test_fs-consistency`                                                                                                                                                        |
| **DESCRIPTION** | Checks current installation directories integrity. The controller checks if all mandatory directories are present, and if their permissions allow the apache process to read/write as required. |

#### `init_db`

| **PATH**        | `core\actions\init\db.php`                                                                                                                                                                                                   |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_db`                                                                                                                                                                                                                |
| **CLI**         | `$ ./equal.run --do=init_db`                                                                                                                                                                                                 |
| **DESCRIPTION** | Creates a database using the details provided in config file. This controllers calls db-connectivity and if connection can be established with the host, it requests the creation of the database, if it does not exist yet. |

## Debug Mode Configuration

It is possible to filter the kind of errors that are present in the [console](http://equal.local/console.php), by setting the DEBUG_MODE parameter in your `config.inc.php` file.

```
define('DEBUG_MODE', EQ_DEBUG_PHP | EQ_DEBUG_ORM | EQ_DEBUG_SQL | EQ_DEBUG_APP);
```

The DEBUG_MODE constant expects a binary mask with the following values : 

| **VALUE**    | **MEANING**                                                                                                                              |
| ------------ | ---------------------------------------------------------------------------------------------------------------------------------------- |
| EQ_DEBUG_PHP | The layer is PHP code, the lowest one. **ex** : `trigger_error()`                                                                        |
| EQ_DEBUG_SQL | The layer is SQL errors.                                                                                                                 |
| EQ_DEBUG_ORM | The layer is eQual's ObjectManager. The [validation](../../application-developers/core-development-guide/validation-searching/validation.md) method also handles orm errors. |
| EQ_DEBUG_APP | The application layer is handled by eQual's [controllers](../../application-developers/core-development-guide/controllers-routing/controllers.md) (data & actions folders).                                    |

Collecting workspace informationThe content of the external-libraries.md file can be divided into smaller sections and integrated into your architecture as follows. I will suggest where each part fits best, and in some cases, I will propose slight modifications to titles or text to align with the context of the target section.

## Using External Libraries with eQual

eQual supports the [PSR-4 standard](https://www.php-fig.org/psr/psr-4/) for auto-loading.  
[Composer](https://getcomposer.org/) can be used to add dependencies inside the `/vendor` folder located in the root folder of the eQual installation.

Libraries that follow the PSR-4 standard can be loaded with a simple `use` statement. For libraries that do not provide such support, you can use `require` or `include` as stated in the library's documentation.

To add a library, use the `require` command to update the `composer.json` file. For example:

```json
"require": {
    "swiftmailer/swiftmailer": "^6.2"
}
```

Then, initialize Composer in the root of the eQual folder:

```bash
$ ./equal.run --do=init_composer
```

## Manual Inclusion of Non-PSR-4 Libraries

For libraries that do not support auto-loading, it is recommended to store them in the `/vendor` folder and include them manually using `require` or `include`. For example:

```php
<?php
require_once '../vendor/swiftmailer/swiftmailer/lib/swift_required.php';
```

Once included, you can still use the `use` statement to simplify the syntax:

```php
<?php
require_once '../vendor/swiftmailer/swiftmailer/lib/swift_required.php';

use \Swift_SmtpTransport as Swift_SmtpTransport;
use \Swift_Message as Swift_Message;
use \Swift_Mailer as Swift_Mailer;
```

### Enabling Passkey Authentication

- Passkey authentication is disabled by default. Enable the setting `core.security.passkey_creation` to activate it.
- To test passkey authentication in development mode, use the `localhost` hostname instead of `equal.local`. Note that passkey authentication in a browser requires HTTPS protocol, but `localhost` is an exception that allows HTTP.

---