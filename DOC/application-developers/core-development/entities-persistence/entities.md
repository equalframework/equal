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
| getOperationPolicies() | Returns a map of secured generic read, update and delete operations with one or more policies the entity must comply with.                                     |
| getFlags()           | Returns structural flags that describe transversal characteristics of the entity.                         |
| getCapabilities()    | Returns structural CRUD capabilities for generic Collection operations.                                   |
| getSchema()          | Returns the full schema of the entity, including system fields.                                           |
| getSettingDefaults() | Returns an associative array of setting defaults for fields.                                              |

## Overridable Methods

| **Method**       | **Description**                                                                   |
| ---------------- | --------------------------------------------------------------------------------- |
| canread()        | Check whether the current user can read the object. Returns an array of errors.   |
| cancreate()      | Check whether the current user can create the object. Returns an array of errors. |
| canupdate()      | Check whether the current user can update the object. Returns an array of errors. |
| candelete()      | Check whether the current user can delete the object. Returns an array of errors. |
| canclone()       | Check whether the current user can clone the object. Returns an array of errors.  |
| oncreate()       | Hook invoked after object creation for performing additional operations.          |
| onbeforeupdate() | Hook invoked before object update for performing additional operations.           |
| onupdate()       | Alias of `onBeforeUpdate()`.                                                      |
| onafterupdate()  | Hook invoked after object update for performing additional operations.            |
| onbeforedelete() | Hook invoked before object deletion for performing additional operations.         |
| ondelete()       | Alias of `onBeforeDelete()`.                                                      |
| onafterdelete()  | Hook invoked after object deletion for performing additional operations.          |
| onclone()        | Hook invoked after object cloning for performing additional operations.           |

## Custom Methods

Custom methods can be added to classes to extend functionality beyond the default behavior.

It is **strongly recommended** to define these methods with **`private` scope** to ensure they are not inadvertently called from outside the class or exposed as public endpoints. This preserves encapsulation and avoids conflicts with core methods or framework naming conventions.

Private methods can still be invoked internally within the class, including from lifecycle hooks or custom logic.



## Entity Lifecycle and Technical Persistence

An entity definition is a **logical contract**, not a physical barrier around its database table. The entity class defines how an object is expected to evolve through:

* its schema, required fields, constraints and unique keys;
* its structural CRUD capabilities;
* its ACL and operation policies;
* its business-validity guards (`canCreate()`, `canRead()`, `canUpdate()`, `canDelete()`);
* its actions, workflow transitions and lifecycle callbacks.

For user-facing `create`, `read`, `update` and `delete` operations, `Collection` interprets and enforces this contract before delegating persistence to `ObjectManager`. Explicit technical lifecycle calls use a shorter path:

```text
Lifecycle-aware Collection request
    → Collection
        → capabilities, ACLs and policies
        → data validation when applicable
        → canCreate / canRead / canUpdate / canDelete
        → ObjectManager
            → database

Explicit draft / write / instantiate
    → Collection
        → declared structural and access checks
        → matching ObjectManager lifecycle primitive
            → database

Trusted technical code
    → ObjectManager
        → database
```

Consequently, a rule such as `getCapabilities()[EQ_R_UPDATE] = false` or an error returned by `canUpdate()` prevents a generic `Collection::update()`; it does **not** make the stored record technically immutable. Trusted framework code can still use `ObjectManager` for a migration, synchronization, repair, dedicated action or other controlled system operation.

This technical path is deliberate. It lets the framework perform changes that do not represent a user-requested business operation. The caller then owns the authorization and business checks that `Collection` would normally provide.

“Technically possible” does not mean “guaranteed to succeed” or “free of every rule.” Database constraints, schema adaptation, valid identifiers and the invariants implemented by the selected `ObjectManager` method still apply. For example, `instantiate()` checks the draft before changing its technical state, whereas `write()` deliberately avoids data validation and callbacks.

In this context, **technical lifecycle** refers to the framework-level `state` (`draft`, `instance`, `archive`), persistence steps and callbacks. It is distinct from the entity's domain-specific business workflow, usually represented by `status`; see [Object Lifecycle vs Business Workflow](../business-logic/workflows/workflows.md#object-lifecycle-vs-business-workflow).



## Entity-Level Access Control

eQual separates structural exposure, authorization, contextual access rules, business validity and persistence.

Generic CRUD operations are exposed through the `Collection` layer. Before delegating an operation to the ORM, `Collection` evaluates several independent mechanisms:

1. **Capabilities** define whether the generic operation is structurally exposed for the entity.
2. **Access control** checks whether the current user has the required rights.
3. **Operation policies** check whether the operation is allowed in the current context.
4. **Operation guards** such as `canCreate()`, `canRead()`, `canUpdate()` and `canDelete()` check whether the requested operation is valid for the target object in its current business state.
5. **ObjectManager** executes the low-level persistence operation.

These mechanisms are complementary and must not be used as substitutes for one another.

| Mechanism                                                | Responsibility                                                                          |
| -------------------------------------------------------- | --------------------------------------------------------------------------------------- |
| `getCapabilities()`                                      | Defines the maximum generic CRUD surface exposed through `Collection`.                  |
| `AccessController`, ACL, groups and roles                | Determine whether the current user has the required rights.                             |
| `getPolicies()`                                          | Declares the policies available on the entity.                                          |
| `getOperationPolicies()`                                 | Associates generic `Collection` operations with one or more policies.                   |
| `canCreate()`, `canRead()`, `canUpdate()`, `canDelete()` | Apply local business guards depending on the current object state and requested values. |
| `getActions()` and workflows                             | Define named business operations and state transitions.                                 |
| `ObjectManager`                                          | Performs low-level persistence and lifecycle operations.                                |

`ObjectManager` is a privileged persistence service. It does not decide whether a user is allowed to perform an operation. User-facing CRUD operations must go through `Collection`, unless the caller is trusted framework code and has already performed the required authorization and business checks.


### Capabilities

Capabilities define which generic CRUD operations are structurally exposed for an entity through `Collection`.

`getCapabilities()` is only a restrictive mechanism: it can narrow or block the generic CRUD surface exposed by an entity, but it does not grant permissions by itself. Even when ACLs grant a right to a user or group, the entity can still explicitly refuse the corresponding generic operation through its capabilities.

They answer the following question:

> Is this generic operation structurally available for this entity?

They do **not** answer the following questions:

> Does this user or group have the right to perform this operation?

> Is this operation allowed in the current business or security context?

User, group and role permissions remain the responsibility of `AccessController`, ACLs and object roles.

Contextual rules remain the responsibility of policies.

Business validity remains the responsibility of operation guards such as `canCreate()`, `canUpdate()` and `canDelete()`.

Capabilities must therefore be treated as a structural security boundary, not as a business permission system.

Generic `Collection` operations evaluate capabilities before checking ACLs, operation policies where applicable, and dynamic business guards.

```text
Controller
    |
Collection
    |
Capabilities
    |
AccessController / ACL / roles
    |
Operation policies, where applicable
    |
Operation guards: canCreate(), canRead(), canUpdate(), canDelete()
    |
ObjectManager
    |
Database
```

A user must satisfy all applicable layers:

* the structural capability rule;
* the effective authorization rules;
* the operation policies, where applicable;
* the operation guards.

If any layer denies the operation, the operation is rejected.


### Capabilities Are Not ACLs

Capabilities must not encode business groups or user-specific permission rules.

The following approach should be avoided:

```php
EQ_R_UPDATE => [
    'accountants' => ['account_id', 'vat_rate'],
    'sales'       => ['price', 'discount']
]
```

This would turn `getCapabilities()` into a second authorization system and would duplicate the role of ACLs, groups, roles or policies.

Instead, capabilities should only describe the maximum generic CRUD surface exposed by the entity.

For example:

```php
public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => true,
        EQ_R_READ   => true,
        EQ_R_UPDATE => true,
        EQ_R_DELETE => false,
        EQ_R_MANAGE => true
    ];
}
```

This means that generic create, read, update and manage operations are structurally exposed, while generic delete is structurally blocked.

It does not mean that every user can perform those operations. The effective authorization still depends on ACLs, operation policies where applicable, and business guards.


### Capabilities Are Not Policies

Capabilities should not encode contextual or business-specific rules.

The following concepts should not be added as capability contexts:

```text
same_organization
same_condo
accounting_period_open
invoice_not_posted
feature_enabled
requires_mfa
booking_operator
accountant
project_manager
```

These concepts belong to ACLs, roles, operation policies, action policies, workflow transition rules or business guards.

Capabilities should remain stable, structural and deliberately limited.

Operation policies should be used when an operation is structurally exposed, but must still be conditioned by a wider or more specific rule.


### Policies

Policies are named contextual rules declared by an entity.

Each entity can override:

```php
public static function getPolicies(): array
```

The method returns a catalogue of policies available on the entity.

Example:

```php
public static function getPolicies(): array {
    return [
        'same_organization' => [
            'description' => 'Verifies that the current user belongs to the same organization as the object.',
            'function'    => 'policySameOrganization'
        ],

        'accounting_period_open' => [
            'description' => 'Verifies that the accounting period related to the object is open.',
            'function'    => 'policyAccountingPeriodOpen'
        ],

        'can_publish' => [
            'description' => 'Verifies that all required information is provided before publication.',
            'function'    => 'policyCanPublish'
        ]
    ];
}
```

`getPolicies()` only declares policies. It does not, by itself, decide when those policies are applied.

Policies may be used by:

* generic `Collection` operations through `getOperationPolicies()`;
* fields through the `policies` descriptor attribute;
* named actions through `getActions()`;
* workflow transitions;
* dedicated controllers or internal services.


### Operation Policies

Operation policies associate generic secured `Collection` operations with one or more policies.

Each entity can override:

```php
public static function getOperationPolicies(): array
```

The method returns an array indexed by CRUD right constants.

In the current `Collection` implementation, operation policies are evaluated for:

```php
EQ_R_READ
EQ_R_UPDATE
EQ_R_DELETE
```

`EQ_R_CREATE` and `EQ_R_MANAGE` may still appear in capabilities, ACLs or action checks, but they are not evaluated by `Collection::assertOperationPolicies()`.

If an operation is not present in the map, no additional operation policy is checked for that operation.

An operation rule can use one of these forms:

| Rule form                  | Meaning                                                                                 |
| -------------------------- | --------------------------------------------------------------------------------------- |
| `true`                     | Allow the operation without an additional policy at this layer.                         |
| `false`                    | Deny the operation at this layer.                                                       |
| `['policy_a', 'policy_b']` | Allow the operation only if all listed policies are satisfied.                          |
| `['*' => ...]`             | Define a scoped rule map. `*` is the default rule for the whole operation.              |

For `EQ_R_UPDATE`, scoped rule maps may also use field names:

```php
public static function getOperationPolicies(): array {
    return [
        EQ_R_READ => [
            'same_organization'
        ],

        EQ_R_UPDATE => [
            '*' => [
                'same_organization'
            ],

            'name' => true,

            'amount' => [
                'same_organization',
                'accounting_period_open'
            ],

            'internal_reference' => false
        ],

        EQ_R_DELETE => [
            '*' => [
                'same_organization',
                'can_be_deleted'
            ]
        ]
    ];
}
```

In this example:

* the generic read operation is allowed only if the `same_organization` policy is satisfied;
* generic updates inherit the `*` rule and therefore require `same_organization`;
* updating `name` does not require an additional operation policy beyond capabilities, ACLs and guards;
* updating `amount` requires both `same_organization` and `accounting_period_open`;
* updating `internal_reference` is denied by operation policy;
* the generic delete operation is allowed only if both `same_organization` and `can_be_deleted` are satisfied.

For `EQ_R_UPDATE`, a field without an explicit rule inherits the `*` rule. If `*` is missing, the default is `true`.

For `EQ_R_READ` and `EQ_R_DELETE`, a scoped rule map only uses the `*` rule. If `*` is missing, no operation policy is checked.

Each policy name must refer to a policy declared by `getPolicies()`. `Collection` checks policies through `AccessController::isCompliant()` for the target class, target ids and current user. All listed policies must pass. If a policy returns inconsistencies, the operation is rejected.

Operation policies are evaluated after capabilities and ACLs, and before operation guards such as `canRead()`, `canUpdate()` or `canDelete()`.

They answer the following question:

> May the current user attempt this generic operation in the current context?

They should be used for contextual, transversal or reusable rules, such as:

```text
same_organization
same_condo
accounting_period_open
feature_enabled
requires_mfa
not_guest
owned_by_current_customer
```

They should not be used to replace ACLs, capabilities or local business guards.


### Operation Policies vs Operation Guards

Operation policies and `can...()` methods may look similar, but they have different responsibilities.

| Mechanism                                                | Responsibility                                                                                           | Typical examples                                                                                             |
| -------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------ |
| `getOperationPolicies()`                                 | Determines whether the current user may attempt the operation in the current context.                    | same organization, same condominium, period open, MFA required, feature enabled                              |
| `canCreate()`, `canRead()`, `canUpdate()`, `canDelete()` | Determines whether the requested operation is valid for the target object in its current business state. | posted invoice cannot be modified, locked entry cannot change account, cancelled booking cannot be confirmed |

A policy should preferably be:

* named;
* reusable;
* contextual;
* applicable across several operations, actions or entities.

A `can...()` guard should preferably be:

* local to the entity;
* close to the business invariant;
* dependent on the current object state;
* able to inspect the requested values.

For example:

```php
public static function getOperationPolicies(): array {
    return [
        EQ_R_UPDATE => [
            'same_condo',
            'accounting_period_open'
        ]
    ];
}
```

The policies above check whether the user operates within the correct perimeter and whether the accounting context allows modifications.

The entity may still define a local guard:

```php
public static function canUpdate($self, array $values = []): array {
    $errors = [];

    if(isset($values['journal_id'])) {
        foreach($self as $id => $entry) {
            if($entry['is_posted']) {
                $errors[] = 'Posted entries cannot change journal.';
            }
        }
    }

    return $errors;
}
```

The guard above checks a local business invariant specific to the entity and to the submitted values.

Avoid policies named only after CRUD operations, such as:

```text
can_update
can_delete
can_read
```

unless they express a specific reusable rule.

Prefer more explicit policy names:

```text
same_organization
same_condo
accounting_period_open
editable_scope
requires_mfa
```


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

For example, an entity marked with `EQ_FLAG_SYSTEM` should usually expose a very limited generic CRUD surface and rely on dedicated controllers, named actions, workflow transitions or privileged internal services for sensitive operations.

---

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

---

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

Supported capability contexts are intentionally limited.

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

They must not describe domain-specific contextual rules such as:

```text
same_condo
same_organization
period_open
invoice_not_posted
```

These concepts belong to ACLs, groups, object roles, operation policies, action policies, workflow rules or business guards.

A new capability context should only be added if it describes a generic structural relation that can apply across multiple domains.


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

The list of fields exposed by capabilities is the maximum generic update surface. ACLs, field descriptors, operation policies and business guards may further restrict the operation, but they must not expand this surface.

Conceptually:

```text
effective_fields = capabilities ∩ ACL ∩ field_rules ∩ operation_policies ∩ business_guards
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

Capability rules are grant-only. Denials should be expressed by not granting a capability, or by using ACLs, operation policies or business guards where a contextual denial is needed.


### Evaluation Order

For a generic operation:

1. `Collection` retrieves the rule from `Model::getCapabilities()`.
2. `true` exposes the operation structurally.
3. `false` blocks the operation structurally.
4. A contextual capability map is evaluated with `AccessController::hasContext()`.
5. For `CREATE`, `READ`, `DELETE` and `MANAGE`, one matching context with `true` exposes the operation.
6. For `UPDATE`, allowed fields are built from every matching context.
7. ACLs are checked with `AccessController::isAllowed()`.
8. Operation policies returned by `Model::getOperationPolicies()` are evaluated for the requested operation when supported by the secured `Collection` path (`READ`, `UPDATE`, `DELETE`).
9. Field-level policies are evaluated when explicitly required by field descriptors.
10. For `CREATE` and `UPDATE`, data validation is executed when applicable. `READ` and `DELETE` do not validate field values.
11. The matching CRUD operation guard (`canCreate()`, `canRead()`, `canUpdate()` or `canDelete()`) is executed.
12. The operation is delegated to `ObjectManager`.

Capabilities, ACLs, policies and guards are complementary:

| Mechanism                          | Question answered                                                                     |
| ---------------------------------- | ------------------------------------------------------------------------------------- |
| `Capabilities`                     | Is the generic operation structurally exposed?                                        |
| `AccessController` / ACL           | Does the current user have the required rights?                                       |
| `Operation policies`               | May the current user attempt this operation in the current context?                   |
| `canUpdate()`, `canCreate()`, etc. | Is the requested operation valid for the target object in its current business state? |
| `ObjectManager`                    | How is the operation technically executed?                                            |

A user must satisfy every applicable layer.


### Collection and ObjectManager Responsibilities

`Collection` is the secured façade for generic user-facing CRUD operations.

It is responsible for:

* checking capabilities;
* checking ACL rights through `AccessController`;
* applying operation policies;
* applying field-level policies when required;
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


### Lifecycle Contract by Operation

`CRUD` (`create`, `read`, `update`, `delete`) and `DWIR` (`draft`, `write`, `instantiate`, `remove`) are only informal mnemonics. They are not framework concepts, formal API categories or guarantees by themselves.

The actual distinction is defined by two parts of the operation contract:

1. whether the `Collection` method calls `assertLifecycle()` and therefore executes the matching entity guard;
2. whether the operation changes the technical `state` implicitly, changes it explicitly, or preserves it.

| Call            | `assertLifecycle()` in `Collection` | Technical-state contract |
| --------------- | ----------------------------------- | ------------------------ |
| `create()`      | Yes → `canCreate()`                 | If `state` is omitted, the new object becomes an `instance`; an explicit `state: draft` keeps it as a draft. |
| `read()`        | Yes → `canRead()`                   | Preserves `state`. |
| `update()`      | Yes → `canUpdate()`                 | Targets `instance` when `state` is omitted. Updating a draft therefore instantiates it unless `state: draft` is explicit. An instance cannot be returned to draft through `update()`. |
| `delete()`      | Yes → `canDelete()`                 | Does not change `state`: by default it sets `deleted`; permanent deletion delegates to `remove()`. |
| `draft()`       | No                                  | Explicitly creates the object with `state: draft`. |
| `write()`       | No                                  | Preserves `state`; this method cannot write the `state` field. |
| `instantiate()` | No                                  | Explicitly changes `draft` to `instance` after its required-field and uniqueness checks. |
| `remove()`      | Not exposed by `Collection`         | Performs no state transition; it permanently removes the record. |

This `assertLifecycle()` check is specifically the call to the entity's `canCreate()`, `canRead()`, `canUpdate()` or `canDelete()` method. It is distinct from field validation based on types, usages, constraints, required fields and unique keys.

The mnemonic is still useful: the four similarly named `Collection` methods currently call `assertLifecycle()`, while the letters D-W-I-R recall the explicit technical operations that do not. The implementation contract above—not the acronym—is authoritative.

Absence of `assertLifecycle()` does not mean “no checks at all.” The `Collection` methods `draft()`, `write()` and `instantiate()` still apply their declared capabilities and ACL checks; `write()` also applies update operation policies. Their validation and callbacks remain method-specific. A refresh performed after an operation may also execute the normal `read()` contract, but it does not add a creation or update lifecycle guard to that operation.

`remove()` is intentionally only a low-level `ObjectManager` primitive. Use it only when permanent removal without `canDelete()` and deletion callbacks is explicitly intended.

The decisive boundary is therefore the actual contract of the called method and the layer on which it is called. A direct `ObjectManager` call is a privileged technical operation and never passes through `Collection::assertLifecycle()`.

Preferred user-facing pattern:

```php
MyEntity::ids($ids)->update($values);
```

Privileged internal pattern:

```php
$orm->update(MyEntity::getType(), $ids, $values);
```

The second form should be reserved for trusted framework internals, migrations, system controllers, computed field recalculations, maintenance operations or code that has already performed authorization checks.


### Privilege Elevation

Capabilities apply to generic user-facing CRUD operations.

When a capability blocks a generic operation, the operation may still be performed by trusted framework code under controlled privilege elevation, provided that the caller is a dedicated action, workflow transition, controller, migration or system process that has already performed the required authorization and business checks.

For example, an entity may block generic updates:

```php
public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => false,
        EQ_R_READ   => true,
        EQ_R_UPDATE => false,
        EQ_R_DELETE => false,
        EQ_R_MANAGE => false
    ];
}
```

This means that the entity cannot be updated through generic user-facing `Collection::update()` calls.

It does not mean that the entity is technically immutable. A dedicated trusted operation may still update it internally after checking its own permissions, policies and business rules.

Example:

```php
$auth->su();

try {
    $orm->update($object_class, $object_ids, $values);
}
finally {
    $auth->su(false);
}
```

Privilege elevation must be scoped to the specific internal operation.

It must not be used as a general bypass of capabilities, ACLs, policies or business guards.

A trusted elevated operation should follow this pattern:

```text
Dedicated controller / action / workflow transition
    |
Check permission to execute the dedicated operation
    |
Check action or transition policies
    |
Check business validity
    |
Enter controlled privilege elevation
    |
Perform the specific internal persistence operation
    |
Leave privilege elevation
```

Acceptable use cases include:

* dedicated business controllers;
* named actions;
* workflow transitions;
* migrations;
* system processes;
* maintenance operations;
* computed field recalculations;
* trusted internal services.

User-facing code must not use privilege elevation to bypass `Collection`.


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

Actions and workflow transitions may internally perform operations that are blocked for generic CRUD usage, provided that they are trusted, specific, and have performed the required authorization and business checks before using privileged persistence.


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

System entities should usually be changed through dedicated controllers, named actions, workflow transitions or privileged internal services instead of generic CRUD operations.


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

This means generic CRUD operations are structurally exposed. The effective permissions still depend on ACLs, operation policies where applicable, and business guards.


#### Business Entity with Operation Policies

A business entity can expose generic CRUD operations while conditioning them with policies:

```php
public static function getCapabilities(): array {
    return [
        EQ_R_CREATE => true,
        EQ_R_READ   => true,
        EQ_R_UPDATE => true,
        EQ_R_DELETE => false,
        EQ_R_MANAGE => true
    ];
}

public static function getPolicies(): array {
    return [
        'same_organization' => [
            'description' => 'Verifies that the current user belongs to the same organization as the object.',
            'function'    => 'policySameOrganization'
        ],

        'editable_state' => [
            'description' => 'Verifies that the object is in a state that allows generic edition.',
            'function'    => 'policyEditableState'
        ]
    ];
}

public static function getOperationPolicies(): array {
    return [
        EQ_R_READ => [
            'same_organization'
        ],

        EQ_R_UPDATE => [
            'same_organization',
            'editable_state'
        ]
    ];
}
```

In this example:

* capabilities expose generic read and update structurally;
* ACLs still determine whether the user has read or update rights;
* operation policies condition those rights according to the current context;
* local guards may still reject the operation based on entity-specific business rules.


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

Operation policies may still further restrict the operation.


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

The `creator` context only exposes the structural capability. The user must still satisfy the required ACL rights and operation policies.


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
* use operation or field policies for contextual access checks;
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
* `policies` can restrict access to fields based on contextual rules.


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

For generic `Collection` operations, policies should be attached through `getOperationPolicies()`.

For field-specific rules, policies may be attached through field descriptors.

For named business operations, policies should be attached through `getActions()` or workflow transition definitions.


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

The `access` attribute may restrict field access, but it does not replace entity-level capabilities, ACLs or policies.


### ACL at Package Initialization

For classes requiring initial Access Control Lists and rights based on users or groups, include related JSON files in the `./init` folder of the package so they can be imported at package initialization.

ACLs should be used to answer questions such as:

* which groups can read this class;
* which users can manage this object;
* which roles imply update rights;
* which wildcard permissions apply to a package or namespace.

Capabilities should not be used for these questions.

Operation policies should not be used for static group or role assignment either. They should only condition the exercise of an existing right in a given context.


## Access Control Performance Guidelines

Permission checks may be expensive.

Capabilities, ACLs, operation policies, field-level policies and operation guards can involve object loading, role resolution, group resolution, context evaluation, policy handlers, computed fields or additional database queries. When several mechanisms are combined unnecessarily, generic CRUD operations may become significantly slower, especially on large collections or batch operations.

For this reason, access-control mechanisms should be used deliberately.

As a general rule, each entity should only define the access-control methods that are relevant to its actual business logic.

Avoid stacking mechanisms that answer the same question.

For example:

* do not use `getCapabilities()` to encode rules that belong to ACLs or policies;
* do not use operation policies for rules that are already fully covered by capabilities;
* do not use `canupdate()` to repeat checks already performed by operation policies;
* do not add field-level operation rules unless field-specific restrictions are actually needed;
* do not attach policies to generic CRUD operations when the same rule is only relevant to a dedicated action or workflow transition.

Prefer the simplest applicable mechanism:

| Need                                                  | Preferred mechanism                                      |
| ----------------------------------------------------- | -------------------------------------------------------- |
| Block or expose a generic CRUD operation structurally | `getCapabilities()`                                      |
| Grant rights to users, groups or roles                | ACLs / `AccessController`                                |
| Condition an operation by reusable contextual rules   | `getOperationPolicies()`                                 |
| Restrict specific fields for an operation             | field-scoped operation rules                             |
| Enforce local business invariants                     | `cancreate()`, `canread()`, `canupdate()`, `candelete()` |
| Execute named business behavior                       | `getActions()` or workflow transitions                   |

Entities should not override access-control methods only for completeness.

A method should be overridden only when it expresses a meaningful rule for the entity.

For standard business entities, inheriting the default behavior is often preferable. Additional capabilities, policies or guards should be introduced only when the entity has a clear structural, contextual or business requirement.

In batch operations, the cost of permission checks can grow quickly. Policy handlers and `can...()` guards should therefore avoid unnecessary per-object queries and should prefer bulk reads whenever possible.

Access-control logic should remain explicit, but not excessive.
