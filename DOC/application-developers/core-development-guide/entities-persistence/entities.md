# Entities

Entities are defined as PHP classes declared within related `.class.php` files. Entity definitions are located in the `/packages/{package_name}/classes` folder of the package they relate to (see [Directory Structure](../../../community/internal-architecture/framework-internals.md)).

All classes inherit from a common `Model` ancestor declared in the `equal\orm` namespace and defined in `/lib/equal/orm/Model.class.php`. These base classes structure the data into various fields (`Field`).

A class is always referred to as an **entity** and belongs to a specific package. Packages and their subdirectories serve as namespaces.

**Example:**
```
core\setting\SettingValue
```

---

## Class Definition

A class consists of a series of field definitions along with specific methods. Class definitions from the same package are placed in the `/packages/{package_name}/classes/` folder.

The structure of an entity is based on its fields, defined using descriptors in an associative array returned by the **`getColumns()`** method.

Below is an example of a hypothetical entity named `Category`:

```php
<?php
namespace sale\catalog;

use equal\orm\Model;

class Category extends Model {
    
    public static function getColumns() {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => "Name of the category (for all variants).",
                'required'          => true
            ],

            'description' => [
                'type'              => 'string',
                'description'       => "A few details about category purpose and usage."
            ],
            
            'product_models_ids' => [ 
                'type'              => 'many2many', 
                'foreign_object'    => 'sale\catalog\ProductModel', 
                'foreign_field'     => 'categories_ids', 
                'rel_table'         => 'sale_product_rel_productmodel_category', 
                'rel_foreign_key'   => 'productmodel_id',
                'rel_local_key'     => 'category_id',
                'description'       => 'List of product models assigned to the category.'
            ]
        ];
    }
}
```

---

## Entity Storage

eQual uses an `ObjectManager` service that implements the Active Record pattern through its Object-Relational Mapping (ORM) system.

Classes are mapped to database tables, with each table's structure (columns) matching the fields defined in the model. Consistency between models (`*.class.php` files) and the database schema is verified at package initialization—column types must be compatible.

When a new class is created or the schema of a class is modified, the SQL schema must be adapted accordingly. The controllers `core_init_package` and `utils_sql-schema` help with this task.

Before initializing a package, check that the configured database is accessible:

```bash
php run.php --do=test_db-access
```

If the command exits with status `0`, the database configured in `config/config.json` is accessible. If the configured database does not exist yet, make sure `config/config.json` exists and contains valid database settings, then create the database:

```bash
php run.php --do=init_db
```

After creating a class or modifying any `.class.php` model behavior, reinitialize the impacted package so the database schema and package metadata are refreshed:

```bash
php run.php --do=init_package --package={package} --force=true
```

!!! tip "Consistency Testing"
    The action controller `core_test_package-consistency` can help spot any incompatibility or inconsistency in class definitions within a given package.

---

## System Fields

Some fields are mandatory and defined in the `Model` base class:

| **Name** | **Type**   | **Role**                                                                                                  |
| -------- | ---------- | --------------------------------------------------------------------------------------------------------- |
| id       | `integer`  | Unique identifier of the object.                                                                          |
| name     | `string`   | (optional) Name used when referring to the object (in views). By default, this field is an alias of `id`. |
| state    | `string`   | One of: `'draft'`, `'instance'`, or `'archive'`.                                                          |
| created  | `datetime` | Date at which the object was created.                                                                     |
| creator  | `many2one` | Reference to `core\User`.                                                                                 |
| modified | `datetime` | Date on which the object was last modified.                                                               |
| modifier | `many2one` | Reference to `core\User`.                                                                                 |
| deleted  | `boolean`  | Marks the object as soft-deleted.                                                                         |

!!! note "About the 'name' field"
    The `name` field can be redefined as an alias or a computed field to provide more meaningful object identification.

### Optional System Fields

Some fields are reserved but optional, with established conventions:

- **state**: Used when a [workflow](../business-logic/workflows/workflows.md) applies to an entity.
- **alert**: Used in conjunction with `core\alert` entities. If defined, it is expected to be a computed field.

---

## Getter Methods

By convention, **getter methods** (`getSomething()`) are always declared with **`public` scope**. These are the only methods that may be accessed from outside the class by external code, other than internal calls made by the ORM (i.e., the `ObjectManager`).

This convention ensures a clear and controlled interface for exposing object data while maintaining strict encapsulation of internal logic.

| **Method**           | **Description**                                                                                           |
| -------------------- | --------------------------------------------------------------------------------------------------------- |
| getName()            | Get model readable name.                                                                                  |
| getDescription()     | Get model description.                                                                                    |
| getType()            | Provide the list of unique rules (array of field combinations).                                           |
| getLink()            | Get the URL associated with the class.                                                                    |
| getColumns()         | Returns the user-defined part of the schema (fields list with types and attributes).                      |
| getSpecialColumns()  | Returns the mandatory (system) fields for all models                                                      |
| getConstraints()     | Returns a map of constraint items associating fields with validation functions.                           |
| getUnique()          | Provide the list of unique rules (list of arrays of field combinations).                                  |
| getFields()          | Returns all field names.                                                                                  |
| getField($name)      | Returns the field descriptor for a given field name.                                                      |
| getValues()          | Returns values of static instance.                                                                        |
| getDefaults()        | Return default values.                                                                                    |
| getTable()           | Return the name of the DB table for storing objects of current class.                                     |
| getWorkflow()        | Returns the [workflow](../business-logic/workflows/workflows.md) associated with the entity.                             |
| getRoles()           | Returns the list of [roles](../business-logic/actions.md#groups-vs-roles) explicitly associated with the entity.           |
| getActions()         | Returns a list of available [actions](../business-logic/actions.md) that can be triggered on the entity.                 |
| getPolicies()        | Returns the [access control policies](../security-access/access-control-lists.md) applicable to the entity. |
| getFlags()           | Returns structural flags that describe transversal characteristics of the entity.                         |
| getCapabilities()    | Returns structural CRUD capabilities for generic Collection operations.                                   |
| getSchema()          | Returns the full schema of the entity, including system fields.                                           |
| getSettingDefaults() | Returns an associative array of setting defaults for fields.                                              |

## Overridable Methods

| **Method**       | **Description**                                                                   |
| ---------------- | --------------------------------------------------------------------------------- |
| canRead()        | Check whether the current user can read the object. Returns an array of errors.   |
| canCreate()      | Check whether the current user can create the object. Returns an array of errors. |
| canUpdate()      | Check whether the current user can update the object. Returns an array of errors. |
| canDelete()      | Check whether the current user can delete the object. Returns an array of errors. |
| canClone()       | Check whether the current user can clone the object. Returns an array of errors.  |
| onCreate()       | Hook invoked after object creation for performing additional operations.          |
| onBeforeUpdate() | Hook invoked before object update for performing additional operations.           |
| onUpdate()       | Alias of `onBeforeUpdate()`.                                                      |
| onAfterUpdate()  | Hook invoked after object update for performing additional operations.            |
| onBeforeDelete() | Hook invoked before object deletion for performing additional operations.         |
| onDelete()       | Alias of `onBeforeDelete()`.                                                      |
| onAfterDelete()  | Hook invoked after object deletion for performing additional operations.          |
| onClone()        | Hook invoked after object cloning for performing additional operations.           |

## Custom Methods

Custom methods can be added to classes to extend functionality beyond the default behavior.

It is **strongly recommended** to define these methods with **`private` scope** to ensure they are not inadvertently called from outside the class or exposed as public endpoints. This preserves encapsulation and avoids conflicts with core methods or framework naming conventions.

Private methods can still be invoked internally within the class, including from lifecycle hooks or custom logic.

---

## Entity-Level Access Control

### Capabilities

Capabilities define, at `Model` level, which generic CRUD operations are structurally exposed for an entity. They also define the user contexts in which those operations are exposed and, for updates, the fields that can be updated through a generic operation.

Capabilities complement the existing permission system (`AccessController`, ACL, roles and policies). They answer a different question: whether the generic operation is structurally available before user rights and business rules are evaluated.

Generic `Collection` operations evaluate capabilities before checking ACLs and before running dynamic business rules such as `cancreate`, `canupdate`, `candelete` or `canread`.

#### Entity Flags

Flags describe structural characteristics of an entity and can alter framework behavior such as generic CRUD exposure, public API visibility, auditing, instantiation rules or table mapping.

The current entity flags are defined in `eq.lib.php`:

```php
define('EQ_FLAG_SYSTEM',     1);  // entity is part of the framework core or security model
define('EQ_FLAG_PRIVATE',    2);  // entity must not be exposed publicly through generic APIs or external integrations
define('EQ_FLAG_ABSTRACT',   4);  // entity is a non-instantiable base model intended only for inheritance
define('EQ_FLAG_AUDIT',      8);  // entity changes should be tracked through Change entries and audit mechanisms
define('EQ_FLAG_OWN_TABLE', 16);  // entity uses a dedicated table instead of sharing the parent table
```

Each entity can override `getFlags()`:

```php
public static function getFlags(): int {
    return EQ_FLAG_SYSTEM | EQ_FLAG_AUDIT;
}
```

The base `Model` also provides a helper:

```php
public static function hasFlag(int $flag): bool {
    return ((static::getFlags() & $flag) === $flag);
}
```

#### Defining Capabilities

Each entity can override:

```php
public static function getCapabilities(): array
```

The method returns an array indexed by CRUD right constants:

```php
EQ_R_CREATE
EQ_R_READ
EQ_R_UPDATE
EQ_R_DELETE
EQ_R_MANAGE
```

Capabilities are intended for generic operations exposed through `Collection`, for example `create()`, `read()`, `update()`, `delete()` and generic controllers that call them. Dedicated controllers can still implement a narrower business workflow for sensitive operations.

#### Capability Grammar

A capability can be global:

```php
EQ_R_CREATE => true
```

The operation is structurally exposed.

```php
EQ_R_DELETE => false
```

The operation is structurally blocked.

A capability can also be contextual:

```php
EQ_R_DELETE => [
    'root' => true
]
```

The operation is exposed only if the `root` context matches. Contextual capabilities are always written as a map:

```php
context => rule
```

The shorthand form below is intentionally avoided because it is less regular and less explicit:

```php
EQ_R_DELETE => ['root']
```

#### Supported Contexts

Capabilities rely on contexts evaluated dynamically by the `AccessController`:

```php
$access->hasContext($context, $object_class, $object_ids);
```

| Context   | Description                                                        |
| --------- | ------------------------------------------------------------------ |
| `root`    | The root user (`EQ_ROOT_USER_ID`).                                 |
| `guest`   | The guest user (`EQ_GUEST_USER_ID`).                               |
| `creator` | The current user is the creator of every object in the collection. |
| `manager` | The current user has `EQ_R_MANAGE` on the collection.              |
| `self`    | The current user is acting on its own `core\User` object.          |

Object-bound contexts such as `creator` must be true for all objects in the collection.

#### Operation Rules

The following operations are evaluated only at operation level:

```php
EQ_R_CREATE
EQ_R_READ
EQ_R_DELETE
EQ_R_MANAGE
```

For these operations, there is no field-level capability. Valid values are:

```php
true
false
[
    'root'    => true,
    'creator' => true,
    'manager' => true,
    'guest'   => true
]
```

A contextual `false` value is allowed but does not grant anything:

```php
EQ_R_DELETE => [
    'root'    => true,
    'manager' => false
]
```

In this example, `root` can delete. The `manager` context grants no delete capability.

`EQ_R_UPDATE` can be evaluated at field level. A contextual rule can expose:

- all technically updatable fields (`true`);
- no field (`false`);
- an explicit list of fields.

```php
EQ_R_UPDATE => [
    'root'    => true,
    'manager' => ['name', 'description', 'status'],
    'creator' => ['name', 'description']
]
```

In this example, `root` can update all technically modifiable fields. `manager` can update `name`, `description` and `status`. `creator` can update only `name` and `description`.

#### Interpreting `false`

In a contextual rule, `false` is not a priority denial. It only means that this context grants nothing.

```php
EQ_R_UPDATE => [
    'root'    => true,
    'creator' => false,
    'manager' => ['firstname', 'lastname']
]
```

If the current user is both `creator` and `manager`, the user can update `firstname` and `lastname`. If the current user is both `root` and `creator`, the user keeps the full update capability granted by `root`.

This avoids reintroducing reject logic in the capability map.

#### Evaluation Order

For a generic operation:

1. The `Collection` retrieves the rule from `Model::getCapabilities()`.
2. `true` exposes the operation structurally.
3. `false` blocks the operation.
4. A contextual map is evaluated with `AccessController::hasContext()`.
5. For `CREATE`, `READ`, `DELETE` and `MANAGE`, one matching context with `true` exposes the operation.
6. For `UPDATE`, allowed fields are built from every matching context.
7. ACLs are then checked with `AccessController::isAllowed()`.
8. Validation and business rules are then executed.
9. The operation is finally delegated to the ORM.

```text
Controller
    |
Collection
    |
Capabilities
    |
AccessController / ACL
    |
Validation
    |
Business hooks
    |
ORM
```

Capabilities and ACLs are complementary:

| Mechanism                     | Question answered                                      |
| ----------------------------- | ------------------------------------------------------ |
| `Capabilities`                | Is the generic operation structurally exposed?         |
| `AccessController` / ACL      | Does the current user have the required rights?        |
| `canupdate`, `cancreate`, etc | Is the operation valid in the current business state?  |
| `ObjectManager`               | How is the operation technically executed?             |

A user must satisfy both capabilities and ACLs.

#### Default Capabilities

The base `Model` exposes all generic CRUD operations by default:

```php
[
    EQ_R_CREATE => true,
    EQ_R_READ   => true,
    EQ_R_UPDATE => true,
    EQ_R_DELETE => true,
    EQ_R_MANAGE => true
]
```

For entities marked with `EQ_FLAG_SYSTEM`, the default is restricted:

```php
[
    EQ_R_CREATE => false,
    EQ_R_READ   => true,
    EQ_R_UPDATE => false,
    EQ_R_DELETE => false,
    EQ_R_MANAGE => false
]
```

System entities should be changed through dedicated controllers instead of generic CRUD operations.

#### Examples

A standard business entity can inherit the default behavior:

```php
public static function getFlags(): int {
    return 0;
}

public static function getCapabilities(): array {
    return parent::getCapabilities();
}
```

A business entity with limited generic updates can define contextual field lists:

```php
public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => true,
        EQ_R_READ   => true,

        EQ_R_UPDATE => [
            'root'    => true,
            'manager' => ['name', 'description', 'status'],
            'creator' => ['name', 'description']
        ],

        EQ_R_DELETE => [
            'root'    => true,
            'manager' => true
        ],

        EQ_R_MANAGE => [
            'root' => true
        ]
    ];
}
```

An entity editable only by its creator can expose a small update surface:

```php
public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => true,
        EQ_R_READ   => true,

        EQ_R_UPDATE => [
            'root'    => true,
            'creator' => ['title', 'content']
        ],

        EQ_R_DELETE => [
            'root'    => true,
            'creator' => true
        ],

        EQ_R_MANAGE => [
            'root' => true
        ]
    ];
}
```

An abstract entity should not expose generic operations directly:

```php
public static function getFlags(): int {
    return EQ_FLAG_ABSTRACT;
}

public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => false,
        EQ_R_READ   => false,
        EQ_R_UPDATE => false,
        EQ_R_DELETE => false,
        EQ_R_MANAGE => false
    ];
}
```

An internal private entity can restrict generic access to `root`:

```php
public static function getFlags(): int {
    return EQ_FLAG_PRIVATE;
}

public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => ['root' => true],
        EQ_R_READ   => ['root' => true],
        EQ_R_UPDATE => ['root' => true],
        EQ_R_DELETE => ['root' => true],
        EQ_R_MANAGE => ['root' => true]
    ];
}
```

The `core\User` entity is a system entity with explicit generic update limits:

```php
public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => false,
        EQ_R_READ   => true,

        EQ_R_UPDATE => [
            'root' => true,
            'self' => ['firstname', 'lastname', 'language', 'password']
        ],

        EQ_R_DELETE => [
            'root' => true
        ],

        EQ_R_MANAGE => [
            'root' => true
        ]
    ];
}
```

In this example, generic user creation is blocked, reading remains structurally exposed, `root` can update all technically modifiable fields, and `self` can update only the listed profile fields. Sensitive fields such as groups, permissions, passkeys, validation state or status must be changed through dedicated controllers.

### Field Access

eQual handles access permissions on a per-object basis. If a user is granted rights on an object, they have those rights on all fields of the object.

If certain information involves distinct usage profiles, consider splitting the object class into smaller entities with distinct rights for each.

**Field behavior modifiers:**

- Fields can have specific behavior based on their descriptor (`readonly`, `required`, `visible`), which can be overridden based on the object's status.
- Actions involving operations on certain fields can be conditioned by [policies](../security-access/authorization-overview.md#policies).
- CRUD (Create, Read, Update, Delete) operations execute `can[...]()` methods, which allow filtering operations based on specific criteria.

### The `policies` Attribute

The `policies` attribute holds a series of policy names. If any policy is not validated for the current user, access to the related field is denied. This is similar to the `visible` attribute but affects data access rather than just UI rendering.

### The `access` Attribute

The `access` attribute defines field accessibility for the current user:

```php
'access' => [
    'groups'     => [], // IDs or names
    'users'      => [], // IDs or logins
    'roles'      => [],
    'visibility' => 'public' // 'public', 'protected', or 'private'
]
```

**Visibility levels:**

- **public**: No restriction (default)
- **protected**: Accessible to authenticated users only
- **private**: Accessible to root user only (system or CLI)—not revealed to regular users

!!! note "ACL at Package Initialization"
    For classes requiring initial Access Control Lists (ACL) and rights based on users and groups, include related JSON files in the `./init` folder of the package for importing those ACL at package initialization.

---

