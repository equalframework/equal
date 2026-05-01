
## API / CLI Reference

### API

Controller Types (CQRS Pattern).
The eQual framework uses three types of controllers, each located in specific directories:

* [Data providers](../../core-development-guide/controllers-routing/#data-providers)
* [Action handlers](../../core-development-guide/controllers-routing/#action-handlers)
* [Application providers](../../core-development-guide/controllers-routing/#application-providers)

#### run.php

The `run.php` script acts as a router to handle custom routes and native DO, GET and SHOW queries, either by CLI, HTTP or PHP.

#### ObjectManager Methods

The `ObjectManager` service provides low-level methods for interacting with entities in the database. These methods allow you to create, search, read, update, and delete objects, offering a powerful abstraction over raw database queries ([see](../../operations/database-storage/data-manipulation-utilities.md)).

These methods are primarily used for backend operations and are not subject to application-level constraints like permissions, workflows, or validations. However, they are essential for building controllers and performing advanced data manipulations.

##### `create`

Creates a new instance of a given class and assigns values to its fields.

```php
/**
 * @param string $entity Class of the object to create.
 * @param array $fields Associative array mapping fields to values.
 * @param string|null $lang Language for multilang fields.
 * @param bool $use_draft Reuse outdated drafts if true.
 * @return int Identifier of the newly created object or error code.
 */
function create($entity, $fields, $lang = null, $use_draft = true)
```


##### `search`

Searches for objects that match specific criteria.

```php
/**
 * @param string $class Class of the objects to search for.
 * @param array|null $domain Criteria the objects must match.
 * @param array $sort Fields and orders for sorting results.
 * @param int $start Offset for the result list.
 * @param int $limit Maximum number of results to return.
 * @return array|int Array of matching object IDs or error code.
 */
function search($class, $domain = null, $sort = ['id' => 'asc'], $start = 0, $limit = 0)
```

##### `read`

Fetches specified field values for selected objects.

```php
/**
 * @param string $class Class of the objects to retrieve.
 * @param mixed $ids Object IDs to retrieve.
 * @param mixed $fields Fields to retrieve.
 * @param string|null $lang Language for multilang fields.
 * @return array|int Associative array of object data or error code.
 */
function read($class, $ids, $fields, $lang = null)
```

##### `update`

Updates specified fields of selected objects.

```php
/**
 * @param string $class Class of the objects to update.
 * @param mixed $ids Object IDs to update.
 * @param array $fields Fields and their new values.
 * @param string|null $lang Language for multilang fields.
 * @return array|int Updated object IDs or error code.
 */
function update($class, $ids, $fields, $lang = null)
```

##### `delete`

Deletes objects permanently or marks them as deleted.

```php
/**
 * @param string $class Class of the objects to delete.
 * @param array $ids Object IDs to delete.
 * @param bool $permanent True for permanent deletion, false for soft deletion.
 * @return array|int Deleted object IDs or error code.
 */
function delete($class, $ids, $permanent = false)
```

##### `validate`

Validates object field values against the model definition.

```php
/**
 * @param string $class Class of the object to validate.
 * @param array $values Field values to validate.
 * @return array|int Invalid fields with error messages or error code.
 */
function validate($class, $values)
```


These methods provide the foundation for interacting with objects in the eQual framework. For higher-level operations, consider using collections or controllers.

---


#### Remote APIs

Example of remote API:

```php
<?php
$body = [
    'access_token'  => MAPBOX_KEY
];

// Send query to Mapbox geocoding API
$request = new HttpRequest('/geocoding/v5/mapbox.places/'.urlencode
($params['address']).'.json', ['Host' => 'api.mapbox.com:443']);

$response = $request
            ->setBody($body)
            ->send();

/* Expected response matches a JSON array of geo+json Features (https://tools.ietf.org/html/ttrfc7946) */
$response->getBody();
```

### CLI References

CLI usage:

```bash
./equal.run [--flag=value] [arguments]
```

#### ObjectManager Methods via CLI

The `ObjectManager` methods can also be invoked via CLI for direct interaction with the database. Below are examples of how to use these methods:

##### Create

```bash
./equal.run --do=create --entity=core\\User --fields="{firstname: 'John', lastname: 'Doe'}"
```

##### Search

```bash
./equal.run --get=model_collect --entity=core\\User --domain="[firstname, '=', 'John']"
```

##### Read

```bash
./equal.run --get=model_read --entity=core\\User --ids="[1, 2, 3]" --fields="[firstname, lastname]"
```

##### Update

```bash
./equal.run --do=update --entity=core\\User --ids="[1]" --fields="{firstname: 'Jane'}"
```

##### Delete

```bash
./equal.run --do=delete --entity=core\\User --ids="[1]" --permanent=true
```

##### Validate

```bash
./equal.run --do=validate --entity=core\\User --fields="{firstname: 'John'}"
```


Collecting workspace information. The testing.md file contains comprehensive information about testing in the eQual framework. To integrate it into your architecture, I suggest splitting it into smaller sections and placing them under appropriate categories. Below is a breakdown of how to structure the content:


#### Running Tests Locally

To run tests locally, open your CLI at the root of eQual (where `run.php` is located) and execute:

```bash
php run.php --do=test_package --package=mypackage
```

### Common Options

This command logs an overview of each test, showing "ok" or "ko" depending on the results.

> **Tip:** Use [http://<localhost>/console.php](http://equal.local/console.php) for insights if the tests fail to run.

All data providers (--get), action handlers (--do) and application providers (--show) can be executed via CLI. 

* **Data providers:** Perform operations/modifications: `--get=<controller_name>`
* **Action handlers:** Retrieve/query data: `--do=<controller_name>`
* **Application providers:** Perform server-side rendering: `--show=<controller_name>`

!!! note "Reminder on naming conventions"
    * Controllers are names as `<package>_<controller_name>`. 
    * Controllers names are derived from PHP file in `packages/<package>/<type>/<controller_name>.php`.


| Flag          | Description                                               |
| ------------- | --------------------------------------------------------- |
| `--get`       | Data provider controller name                             |
| `--do`        | Action handler controller name                            |
| `--show`      | Application provider controller name                      |
| `--package`   | Target package (e.g., `core`, `demo`)                     |
| `--entity`    | Entity class (e.g., `core\\User`)                         |
| `--fields`    | Fields to set/query (array or object notation)            |
| `--ids`       | Object IDs (array notation)                               |
| `--domain`    | Search criteria                                           |
| `--right`     | DB right (`create`, `read`, `update`, `delete`, `manage`) |
| `--level`     | Operation level                                           |
| `--permanent` | Boolean for permanent deletion                            |
| `--announce`  | Boolean for application provider                          |

### DB Rights

Available rights:

* `create`
* `read`
* `update`
* `delete`
* `manage`


You can grant one right for one entity at a time.

```bash
./equal.run --do=group_grant --group=default --right=update --entity="core\User"
```

### Data Providers (`--get`)

Example:
```bash
./equal.run --get=model_collect --entity='core_User'
```

### Action Handlers (`--do`)

Example:
```bash
./equal.run --do=init_db
```

Example (see [anonymization](../core-development-guide/models/data-anonymization.md)):
```bash
./equal.run --do_anonymize --package=core
```

### Application Providers (`--show`)

Example:
```bash
./equal.run --show=core_welcome --announce=1
```


#### Dot Notation

```bash
$ ./equal.run \
--get=model_collect \
--entity=core\\User \
--fields=[id,name,groups_ids.name,groups_ids.description]
```

### Array Notation

```bash
$ ./equal.run \
--get=model_collect \
--entity=core\\User \
--fields="{id,name,groups_ids:{name,description}}" 
```

### Authentication in CLI Contexts

In CLI contexts, authentication is not required. The user is identified as `root` with full privileges, bypassing standard authentication mechanisms.

---
