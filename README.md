eQual is a versatile, language-agnostic and web-oriented open-source low-code framework, designed to efficiently create and manage modern softwares that can adapt to any Application Logic.

⭐ If you find eQual useful, relevant, or simply beautiful, please consider giving us a star on GitHub! Your support encourages us and will help making eQual the most powerful framework ever.  

🛠️ [Contributors welcome!](CONTRIBUTING.md) You want to contribute to a great open-source project? We need help to keep on 🚀, finishing 🚧, fixing 🐛, and make it 🎨  

[![Build Status](https://circleci.com/gh/equalframework/equal.svg?style=shield)](https://circleci.com/gh/equalframework/equal)
![Number of contributors](https://img.shields.io/github/contributors/equalframework/equal)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](https://github.com/equalframework/equal/pulls)
[![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg)](https://www.gnu.org/licenses/lgpl-3.0)
![GitHub commit activity](https://img.shields.io/github/commit-activity/m/equalframework/equal)

<p align="center">
    <img align="center" src="https://github.com/equalframework/equal/blob/master/public/assets/img/equal_logo.png?raw=true" alt="eQual - Create great Apps, your way!" />
</p>

# eQual - Create great Apps, your way!

eQual offers a native Low-Code approach, based on the definition of the application logic and components (rather than on code or the language used).

Data is modeled via entities, to which a large part of the application logic is associated (workflow, roles, events, actions, policies), which are manipulated by the ORM, with which it is possible to interact using CQRS controllers.

In turn, the Controllers can be invoked via an API.

eQual offers tools that allow the visual consultation and editing of the different components, in turn in the form of relational diagrams, and entity, workflow, view, menu, and translation editors.

It also has a rendering engine that allows views and menus to be assembled in order to define a complete application.

This mechanism enables eQual to generate an application without writing a single line of code, providing both a user interface and an API that can be connected to any external service.


## Benefits

**Rock Solid Security** Secure every API endpoint with User Management, Role-Based Access Controls, SSO Authentication, JWT, CORS, and OAuth.

**Server-Side Scripting** Implement custom logic on any endpoint to build your own custom API, and interact with it using your preferred programming language.

**Low-Code Instant APIs** Automatically generate a complete set of ReST API endpoints with live documentation for any SQL or NoSQL database, file storage system, or external service.

## Example

### CLI
`$ ./equal.run --get=demo_first`

### HTTP
`GET /?get=demo_first HTTP/1.1`

### Source
```
<?php
// Tired of steep learning curves?
echo "This Contoller is valid and generates a HTTP compliant response!";
```

## Requirements

eQual requires the following environment:

* **PHP 7+** with extensions mysqli (mandatory) + gd opcache zip tidy (optional)
* **Apache 2+** or **Nginx**
* **MySQL 5+** compatible DBMS (tested up to MySQL 5.7 and MariaDB 10.3)

## Install

Download code as ZIP:
```
wget https://github.com/equalframework/equal/archive/master.zip
```
or clone with Git :
```
git clone https://github.com/equalframework/equal.git
```

For more info, see : [http://doc.equal.run/getting-started/installation](http://doc.equal.run/getting-started/installation/)


## CLI autocomplete
To ease commands typing in CLI, you can enable autocompletion by running `source autocomplete`.


## Code Coverage setup

Config for PHP 7.4 with xdebug

In dockerfile, xdebug can be added with following command
```
RUN pecl install xdebug-3.1.6
```

php.ini
```
zend_extension=xdebug
```

Create tests directory
```
mkdir tests
```

Create `tests/CoverageTest.php` file
```
<?php
use PHPUnit\Framework\TestCase;
use equal\test\Tester;
include('eq.lib.php');

final class CoverageTest extends TestCase {

	public function test(): void {
		$tests_path = "packages/core/tests";
		if(is_dir($tests_path)) {
			foreach (glob($tests_path."/*.php") as $filename) {
				include($filename);
				$tester = new Tester($tests);
				$tester->test()->toArray();
			}
		}
		$this->assertTrue(true);
	}

}
```

composer.json
```
{
    "require": {
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "phpunit/php-code-coverage": "^9.2",
        "symplify/easy-coding-standard": "^11.1"
    }
}
```

Install composer and dependencies
```
./equal.run --do=init_composer
```

phpunit.xml
```
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/9.5/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         cacheResultFile="cache/test-results"
         executionOrder="depends,defects"
         forceCoversAnnotation="false"
         beStrictAboutCoversAnnotation="false"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTodoAnnotatedTests="true"
         convertDeprecationsToExceptions="true"
         failOnRisky="false"
         failOnWarning="true"
         verbose="true">
    <testsuites>
        <testsuite name="default">
            <directory>tests</directory>
        </testsuite>
    </testsuites>

    <coverage cacheDirectory="cache/code-coverage"
              processUncoveredFiles="true">
        <include>
            <directory suffix=".php">lib</directory>
        </include>
    </coverage>
</phpunit>
```

Alternate script generation:
```
./vendor/bin/phpunit --generate-configuration
```


Run coverage tests and HTML reporting
```
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html tests/report
```