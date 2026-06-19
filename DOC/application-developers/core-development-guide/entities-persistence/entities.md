# Entities

Entities are defined as PHP classes declared within related `.class.php` files. Entity definitions are located in the `/packages/{package_name}/classes` folder of the package they relate to (see [Directory Structure](../../../community/internal-architecture/framework-internals.md)).

All classes inherit from a common `Model` ancestor declared in the `equal\orm` namespace and defined in `/lib/equal/orm/Model.class.php`. These base classes structure the data into various fields (`Field`).

A class is always referred to as an **entity** and belongs to a specific package. Packages and their subdirectories serve as namespaces.

**Example:**
```
core\setting\SettingValue
```



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



## Entity-Level Access Control

eQual separates structural exposure, authorization, business validity and persistence.

Generic CRUD operations are exposed through the `Collection` layer. Before delegating an operation to the ORM, `Collection` evaluates several independent mechanisms:

1. **Capabilities** define whether the generic operation is structurally exposed for the entity.
2. **Access control** checks whether the current user has the required rights.
3. **Policies** check transversal or contextual access rules.
4. **Operation guards** such as `cancreate`, `canread`, `canupdate` and `candelete` check whether the operation is valid in the current business state.
5. **ObjectManager** executes the low-level persistence operation.

These mechanisms are complementary and must not be used as substitutes for one another.

| Mechanism                                        | Responsibility                                                                    |
| ------------------------------------------------ | --------------------------------------------------------------------------------- |
| `getCapabilities()`                              | Defines the maximum generic CRUD surface exposed through `Collection`.            |
| `AccessController`, ACL, groups and roles        | Determine whether the current user has the required rights.                       |
| `getPolicies()` and policy checks                | Apply transversal or contextual access constraints.                               |
| `cancreate`, `canread`, `canupdate`, `candelete` | Apply business guards depending on the current object state and requested values. |
| `getActions()` and workflows                     | Define named business operations and state transitions.                           |
| `ObjectManager`                                  | Performs low-level persistence and lifecycle operations.                          |

`ObjectManager` is a privileged persistence service. It does not decide whether a user is allowed to perform an operation. User-facing CRUD operations must go through `Collection`, unless the caller is trusted framework code and has already performed the required authorization checks.



### Capabilities

Capabilities define which generic CRUD operations are structurally exposed for an entity through `Collection`.

They answer the following question:

> Is this generic operation structurally available for this entity?

They do **not** answer the following question:

> Does this user or group have the right to perform this operation?

User, group and role permissions remain the responsibility of `AccessController`, ACLs, object roles and policies.

Capabilities must therefore be treated as a structural security boundary, not as a business permission system.

Generic `Collection` operations evaluate capabilities before checking ACLs and before running dynamic business rules such as `cancreate`, `canupdate`, `candelete` or `canread`.

```text
Controller
    |
Collection
    |
Capabilities
    |
AccessController / ACL / roles
    |
Policies
    |
Operation guards: cancreate, canread, canupdate, candelete
    |
ObjectManager
    |
Database
```

A user must satisfy both:

* the structural capability rule;
* the effective authorization rules.

If either layer denies the operation, the operation is rejected.



### Capabilities Are Not ACLs

Capabilities must not encode business groups or user-specific permission rules.

The following approach should be avoided:

```php
EQ_R_UPDATE => [
    'accountants' => ['account_id', 'vat_rate'],
    'sales'       => ['price', 'discount']
]
```

This would turn `getCapabilities()` into a second authorization system and would duplicate the role of ACLs, groups or policies.

Instead, capabilities should use generic structural contexts:

```php
EQ_R_UPDATE => [
    'root'    => true,
    'manager' => ['name', 'description', 'status'],
    'creator' => ['name', 'description']
]
```

In this example, `manager` does not mean that the user belongs to a business group named "Managers". It means that the user has the `EQ_R_MANAGE` right on the target class or collection. The question of which users or groups receive `EQ_R_MANAGE` remains handled by ACLs, roles and permissions.



### Entity Flags

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

Flags and capabilities are related but distinct:

| Mechanism           | Purpose                                                                 |
| ------------------- | ----------------------------------------------------------------------- |
| `getFlags()`        | Describes structural properties of the entity.                          |
| `getCapabilities()` | Defines which generic CRUD operations are exposed through `Collection`. |

For example, an entity marked with `EQ_FLAG_SYSTEM` should usually expose a very limited generic CRUD surface and rely on dedicated controllers for sensitive operations.



### Defining Capabilities

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

Capabilities are intended for generic operations exposed through `Collection`, for example:

```php
create()
read()
update()
delete()
clone()
```

Dedicated controllers, named actions and workflows can still implement narrower business operations for sensitive processes.

Examples of sensitive operations that should usually be represented as dedicated actions or workflow transitions rather than generic CRUD updates include:

```text
validate
confirm
cancel
post
reopen
archive
transfer
refund
allocate
export
```



### Capability Grammar

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

The operation is exposed only if the `root` context matches.

Contextual capabilities are always written as a map:

```php
context => rule
```

The shorthand form below should not be used because it is less explicit and less regular:

```php
EQ_R_DELETE => ['root']
```



### Supported Capability Contexts

Capabilities rely on contexts evaluated dynamically by `AccessController::hasContext()`:

```php
$access->hasContext($context, $object_class, $object_ids);
```

Supported contexts are intentionally limited.

| Context   | Description                                                           |
| --------- | --------------------------------------------------------------------- |
| `root`    | The current user is the root user (`EQ_ROOT_USER_ID`).                |
| `guest`   | The current user is the guest user (`EQ_GUEST_USER_ID`).              |
| `self`    | The current user is acting on its own `core\User` object.             |
| `manager` | The current user has `EQ_R_MANAGE` on the target class or collection. |
| `creator` | The current user is the creator of every object in the collection.    |

Object-bound contexts such as `self` and `creator` require existing object identifiers. They are therefore not generally applicable to `CREATE`.

For collections containing several objects, object-bound contexts must match all objects in the collection.

For example, `creator` is true only if the current user is the creator of every targeted object.



### Contexts Are Structural, Not Business-Specific

Capability contexts must remain structural and generic.

They may describe:

* a system identity, such as `root` or `guest`;
* a relation between the user and the target object, such as `self` or `creator`;
* an authorization abstraction, such as `manager`, which delegates to ACL rights.

They must not describe business groups such as:

```text
accountant
sales
support_agent
booking_operator
project_manager
```

These concepts belong to ACLs, groups, object roles, policies or business guards.

A new capability context should only be added if it describes a generic structural relation that can apply across multiple domains. For example, a future `owner` or `assignee` context may be acceptable if it is defined through a clear framework-level convention, but it should not be tied to a business-specific group.



### Operation Rules

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

* all technically updatable fields with `true`;
* no field with `false`;
* an explicit list of fields.

```php
EQ_R_UPDATE => [
    'root'    => true,
    'manager' => ['name', 'description', 'status'],
    'creator' => ['name', 'description']
]
```

In this example:

* `root` can update all technically modifiable fields;
* `manager` can update `name`, `description` and `status`;
* `creator` can update only `name` and `description`.

The list of fields exposed by capabilities is the maximum generic update surface. ACLs, policies, field descriptors and business guards may further restrict the operation, but they must not expand this surface.

Conceptually:

```text
effective_fields = capabilities ∩ ACL ∩ field_rules ∩ policies ∩ business_rules
```



### Interpreting `false`

In a contextual rule, `false` is not a priority denial. It only means that this context grants nothing.

```php
EQ_R_UPDATE => [
    'root'    => true,
    'creator' => false,
    'manager' => ['firstname', 'lastname']
]
```

If the current user is both `creator` and `manager`, the user can update `firstname` and `lastname`.

If the current user is both `root` and `creator`, the user keeps the full update capability granted by `root`.

This avoids reintroducing explicit reject logic in the capability map.

Capability rules are grant-only. Denials should be expressed by not granting a capability, or by using ACLs, policies or business guards where a contextual denial is needed.



### Evaluation Order

For a generic operation:

1. `Collection` retrieves the rule from `Model::getCapabilities()`.
2. `true` exposes the operation structurally.
3. `false` blocks the operation structurally.
4. A contextual map is evaluated with `AccessController::hasContext()`.
5. For `CREATE`, `READ`, `DELETE` and `MANAGE`, one matching context with `true` exposes the operation.
6. For `UPDATE`, allowed fields are built from every matching context.
7. ACLs are checked with `AccessController::isAllowed()`.
8. Policies are checked when explicitly required by the operation, field or action.
9. Validation is executed.
10. Operation guards such as `cancreate`, `canread`, `canupdate` or `candelete` are executed.
11. The operation is delegated to `ObjectManager`.

Capabilities and ACLs are complementary:

| Mechanism                      | Question answered                                       |
| ------------------------------ | ------------------------------------------------------- |
| `Capabilities`                 | Is the generic operation structurally exposed?          |
| `AccessController` / ACL       | Does the current user have the required rights?         |
| `Policies`                     | Does the request satisfy contextual access constraints? |
| `canupdate`, `cancreate`, etc. | Is the operation valid in the current business state?   |
| `ObjectManager`                | How is the operation technically executed?              |

A user must satisfy both capabilities and ACLs.



### Collection and ObjectManager Responsibilities

`Collection` is the secured façade for generic user-facing CRUD operations.

It is responsible for:

* checking capabilities;
* checking ACL rights through `AccessController`;
* applying policies when required;
* running operation guards;
* validating values;
* delegating persistence to `ObjectManager`.

`ObjectManager` is the low-level persistence service.

It is responsible for:

* loading and storing fields;
* maintaining the object cache;
* creating, updating, deleting and cloning records;
* handling relations;
* computing stored and instant computed fields;
* triggering lifecycle hooks;
* applying low-level workflow transitions.

`ObjectManager` does not perform user authorization checks by design. Code handling user input should not call low-level `ObjectManager` CRUD methods directly unless authorization has already been explicitly checked.

Preferred user-facing pattern:

```php
MyEntity::ids($ids)->update($values);
```

Privileged internal pattern:

```php
$orm->update(MyEntity::getType(), $ids, $values);
```

The second form should be reserved for trusted framework internals, migrations, system controllers, computed field recalculations, maintenance operations or code that has already performed authorization checks.



### Actions and Workflows

Capabilities govern generic CRUD operations only.

Named business operations should be defined through:

```php
getActions()
```

and checked through action policies or `AccessController::canPerform()`.

Workflow transitions should be defined through:

```php
getWorkflow()
```

and checked through workflow transition rules, transition domains and transition policies.

A workflow transition should not be treated as a generic update of the workflow status field. It represents a named business operation and should be handled as such.

For example, the following operations should usually be implemented as actions or workflow transitions, not as direct generic updates:

```text
confirm
validate
cancel
post
archive
reopen
transfer
refund
```



### Default Capabilities

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

System entities should usually be changed through dedicated controllers, named actions or privileged internal services instead of generic CRUD operations.



### Examples

#### Standard Business Entity

A standard business entity can inherit the default behavior:

```php
public static function getFlags(): int {
    return 0;
}

public static function getCapabilities(): array {
    return parent::getCapabilities();
}
```

This means generic CRUD operations are structurally exposed. The effective permissions still depend on ACLs, policies and business guards.



#### Business Entity with Limited Generic Updates

A business entity can expose only a limited generic update surface:

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

In this example, `manager` does not refer to a business group. It refers to users who have `EQ_R_MANAGE` on the class or collection.



#### Entity Editable by Its Creator

An entity can expose a small update surface to its creator:

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

The `creator` context only exposes the structural capability. The user must still satisfy the required ACL rights.



#### Abstract Entity

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



#### Internal Private Entity

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



#### System User Entity

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

In this example:

* generic user creation is blocked;
* reading remains structurally exposed;
* `root` can update all technically modifiable fields;
* `self` can update only the listed profile fields.

Sensitive fields such as groups, permissions, passkeys, validation state or status must be changed through dedicated controllers, named actions or privileged internal services.



### Field Access

eQual primarily handles access permissions on a per-object basis. If a user is granted rights on an object, those rights apply to the object as a whole.

When different fields require different access profiles, prefer one of the following approaches:

* split the model into smaller entities with distinct access rules;
* expose a limited generic update surface through `getCapabilities()`;
* use policies for contextual access checks;
* use dedicated controllers or actions for sensitive operations;
* use field descriptors for UI and technical behavior.

Field-level capabilities are currently supported only for `EQ_R_UPDATE`.

They should be used to restrict the maximum generic update surface, not to define business group permissions.



### Field Behavior Modifiers

Fields can have specific behavior based on their descriptor:

```php
readonly
required
visible
```

These descriptors affect technical or UI behavior. They do not replace ACLs or capabilities.

For example:

* `readonly` prevents a field from being updated through normal update flows;
* `visible` affects presentation and should not be treated as a security boundary by itself;
* `required` affects validation;
* `policies` can restrict access to fields or operations based on contextual rules.



### The `policies` Attribute

The `policies` attribute holds a series of policy names.

If any policy is not validated for the current user, access to the related field or operation is denied.

Policies are useful for contextual rules that do not belong in capabilities or ACLs.

Examples:

* the user must belong to the same organization as the object;
* the object must be in a specific business state;
* a time-based or environment-based condition must be satisfied;
* a sensitive action requires an additional compliance rule.

Policies should not be used to define the structural CRUD surface of an entity. That responsibility belongs to `getCapabilities()`.



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

Visibility levels:

| Visibility  | Meaning                                           |
| ----------- | ------------------------------------------------- |
| `public`    | No restriction.                                   |
| `protected` | Accessible to authenticated users only.           |
| `private`   | Accessible to root user only, system code or CLI. |

The `access` attribute may restrict field access, but it does not replace entity-level capabilities or ACLs.



### ACL at Package Initialization

For classes requiring initial Access Control Lists and rights based on users or groups, include related JSON files in the `./init` folder of the package so they can be imported at package initialization.

ACLs should be used to answer questions such as:

* which groups can read this class;
* which users can manage this object;
* which roles imply update rights;
* which wildcard permissions apply to a package or namespace.

Capabilities should not be used for these questions.



### Design Rules

The following rules summarize the intended architecture:

1. `getCapabilities()` defines the maximum generic CRUD surface exposed through `Collection`.
2. Capabilities are structural and must not encode business group permissions.
3. ACLs, groups and roles determine who has rights.
4. Policies determine whether contextual access constraints are satisfied.
5. `cancreate`, `canread`, `canupdate` and `candelete` determine whether the operation is valid in the current business state.
6. Named business operations should use `getActions()` or workflows.
7. User-facing CRUD operations should go through `Collection`.
8. `ObjectManager` is a privileged persistence service and should not be used directly from user-facing code unless authorization has already been checked.
9. Field-level capabilities are allowed for `EQ_R_UPDATE` only and represent a maximum generic update surface.
10. Sensitive fields and operations should use dedicated controllers, actions, workflows or internal services rather than generic CRUD exposure.
