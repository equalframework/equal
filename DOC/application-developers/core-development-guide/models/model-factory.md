# Model Factory

The `ModelFactory` provides a convenient way to generate virtual (non-persistent) objects.

Virtual objects exist only in memory and are not saved to the database. This makes them ideal for:

- **Unit testing**: Create test data without database side effects
- **Mocking**: Simulate entities for development or integration testing
- **Prototyping**: Quickly generate sample data to validate application logic

Entities are created with random values by default but can be customized as needed through various options.

---

## Features Overview

- **Randomized Data**: Entities are generated with random attributes based on field [usages](./entities/fields/#usages).
- **Customizable Values**: Specific fields can be forced to predefined values.
- **Batch Creation**: Supports generating multiple entities at once.
- **Sequence Control**: Allows defining custom sequences for multiple entities.
- **Unique Constraint Handling**: Optionally respect or ignore unique constraints.
- **Relation Handling**: Supports the generation of related entities.

---

## Usage Examples

### Basic Entity Creation

Without any additional options, a single entity is generated with random values for all attributes.

```php
<?php
use equal\orm\ModelFactory;

$user = ModelFactory::create('core\User');
```

**Example Output**:

```json
{
    "login": "ukag6qr4@postbox.com",
    "username": "trueBeliever3590",
    "password": "DaAiiahaGO",
    "firstname": "Caroline",
    "lastname": "Nguyen",
    "language": "es",
    "validated": false,
    "status": null
}
```

### Creating Multiple Entities

Generate more than one entity by specifying the `qty` (quantity) option.

```php
<?php
use equal\orm\ModelFactory;

$users = ModelFactory::create('core\User', ['qty' => 2]);
```

**Example Output**:

```json
[
    {
        "login": "lg8nv@postbox.com",
        "username": "silentStorm726",
        "password": "NI6elTCY6w",
        "firstname": "Michael",
        "lastname": "Maillard",
        "language": "ki",
        "validated": false,
        "status": "created"
    },
    {
        "login": "jytxp@webmail.org",
        "username": "superstar874",
        "password": "Em9515qBtr",
        "firstname": "Sawyer",
        "lastname": "Mendoza",
        "language": "is",
        "validated": true,
        "status": "created"
    }
]
```

You can also specify a range for random quantity using array notation:

```php
<?php
use equal\orm\ModelFactory;

// Creates between 1 and 5 users
$users = ModelFactory::create('core\User', ['qty' => [1, 5]]);
```

### Forcing Specific Values

To control certain attributes, use the `values` option. Fields not specified remain randomized.

```php
<?php
use equal\orm\ModelFactory;

$user = ModelFactory::create('core\User', [
    'values' => [
        'firstname' => 'John',
        'lastname'  => 'Doe'
    ]
]);
```

**Example Output**:

```json
{
    "login": "q1a22xm5pm@sample.org",
    "username": "luckyCharm2140",
    "password": "J1IvaJEo3e",
    "firstname": "John",
    "lastname": "Doe",
    "language": "br",
    "validated": false,
    "status": "created"
}
```

### Sequences of Forced Values

The `sequences` option allows you to force different values for each entity in a batch.

```php
<?php
use equal\orm\ModelFactory;

$users = ModelFactory::create('core\User', [
    'qty'       => 2,
    'sequences' => [
        [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'language'  => 'en'
        ],
        [
            'firstname' => 'Jean',
            'lastname'  => 'Dupont',
            'language'  => 'fr'
        ]
    ]
]);
```

**Example Output**:

```json
[
    {
        "login": "wzr0yqsf8@postbox.com",
        "username": "coolUser392",
        "password": "UDrQP3oMNI",
        "firstname": "John",
        "lastname": "Doe",
        "language": "en",
        "validated": true,
        "status": "created"
    },
    {
        "login": "87388@webmail.org",
        "username": "happyCamper930",
        "password": "OYOaKwGRtc",
        "firstname": "Jean",
        "lastname": "Dupont",
        "language": "fr",
        "validated": true,
        "status": "created"
    }
]
```

!!! note "Using Sequences"
    If the `sequences` array is shorter than `qty`, sequences will loop from the beginning.

### Ignoring Unique Constraints

By default, the `ModelFactory` respects `unique` constraints. Set the `unique` option to `false` to override this behavior.

```php
<?php
use equal\orm\ModelFactory;

$groups = ModelFactory::create('core\Group', [
    'qty'    => 2,
    'values' => ['name' => 'Group 1'],
    'unique' => false
]);
```
**Example Output**:
```json
[
    {
        "name": "Group 1",
        "display_name": "Ad veniam tempor",
        "description": "Nisi consectetur ad ut exercitation labore ut ut ut. Incididunt dolor sit ea minim sed amet sit. Nostrud adipiscing"
    },
    {
        "name": "Group 1",
        "display_name": "Ex consectetur sit do do",
        "description": "Adipiscing ipsum ut eiusmod laboris dolor aliquip consectetur minim lorem dolor elit. E"
    }
]
```

### Generating Related Entities

The `relations` option generates related entities along with the parent entity. Related entities support the same options (`qty`, `values`, `sequences`, etc.) as the main entity.

```php
<?php
use equal\orm\ModelFactory;

$group = ModelFactory::create('core\Group', [
    'relations' => [
        'users_ids' => ['qty' => 2]
    ]
]);
```
**Example Output**:
```json
{
    "name": "Aliquip dolo",
    "display_name": "Ipsum sed nostrud adipiscing...",
    "description": "Magna sed minim enim aliqua...",
    "users_ids": [
        {
            "login": "5ow55engy@inbox.net",
            "username": "happyCamper6100",
            "password": "ingB2tK9xS",
            "firstname": "William",
            "lastname": "Gautier",
            "language": "nd",
            "validated": true,
            "status": "created"
        },
        {
            "login": "oae1e@mail.com",
            "username": "stellar-voyager5379",
            "password": "udSpMEk2NM",
            "firstname": "Ella",
            "lastname": "Marechal",
            "language": "ja",
            "validated": false,
            "status": "created"
        }
    ]
}
```

This creates a group with two randomly generated users attached to it.

---

## Options Reference

| Option      | Type             | Default | Description                                                              |
| ----------- | ---------------- | ------- | ------------------------------------------------------------------------ |
| `qty`       | `int` or `array` | `1`     | Number of entities to generate. Use array `[min, max]` for random range. |
| `values`    | `array`          | `[]`    | Field values to force for all generated entities.                        |
| `sequences` | `array`          | `[]`    | Array of value sets applied sequentially to each entity.                 |
| `unique`    | `bool`           | `true`  | Whether to respect unique constraints.                                   |
| `relations` | `array`          | `[]`    | Configuration for generating related entities.                           |

---