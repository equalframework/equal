# Entities

Models are defined as PHP classes declared within related `.class.php` files. The model definitions are located in the `/packages/{package_name}/classes` folder of the package they relate to (see [Directory Structure](../../../../community/internal-architecture/framework-internals.md)).

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

- **state**: Used when a [workflow](../workflows/workflows.md) applies to an entity.
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
| getWorkflow()        | Returns the [workflow](../workflows/workflows.md) associated with the entity.                             |
| getRoles()           | Returns the list of [roles](../actions/#groups-vs-roles) explicitly associated with the entity.           |
| getActions()         | Returns a list of available [actions](../actions.md) that can be triggered on the entity.                 |
| getPolicies()        | Returns the [access control policies](../authorization/access-control-lists.md) applicable to the entity. |
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

### Field Access

eQual handles access permissions on a per-object basis. If a user is granted rights on an object, they have those rights on all fields of the object.

If certain information involves distinct usage profiles, consider splitting the object class into smaller entities with distinct rights for each.

**Field behavior modifiers:**

- Fields can have specific behavior based on their descriptor (`readonly`, `required`, `visible`), which can be overridden based on the object's status.
- Actions involving operations on certain fields can be conditioned by [policies](../authorization/authorization-overview/#policies).
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

