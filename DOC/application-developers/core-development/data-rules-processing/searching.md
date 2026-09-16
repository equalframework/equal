# Searching

The [ORM (Object-Relational Mapping)](../entities-persistence/orm.md) handles searching through a `search()` method available on both the ObjectManager service and Collection objects. This method returns a list of object identifiers matching the specified criteria, which can then be used to retrieve the full objects or perform further operations.

## How Search Works

The `search()` method accepts a `domain` argument that describes the search criteria using a structured format. To understand the domain syntax and advanced filtering options, refer to the [domain documentation](domains.md).

### Structural Model Scope

When the requested class returns a non-null value from `getModelScope()`, the ORM adds an exact condition on the system `model` column. This condition is structural: it is applied in addition to the caller's domain and is added to every disjunctive branch. A user-provided `OR` expression therefore cannot widen the search to another subtype.

For this hierarchy:

```text
Model
└── A              getModelScope() -> null
    └── B          getModelScope() -> B::class
        └── C      getModelScope() -> C::class
```

`A::search()` can inspect the full shared table, while `B::search()` is restricted to rows whose `model` is exactly `B::class`. It does not include C rows unless B explicitly adopts a different scope.

The same restriction applies whether search is called through `ObjectManager` or a `Collection`. Typed identifier-based operations also validate the discriminator, preventing an update or deletion through B from targeting a C record. See [Model Storage and Discrimination](../entities-persistence/orm.md#model-storage-and-discrimination) for table boundaries, behavioral extensions and record-creation rules.

**Search results** consist of object identifiers that can be:

* Used for reading values of filtered objects
* Combined with additional parameters (`lang`, `sort`, `start`, `limit`) to refine results

<div align="center">
    <img src="/_assets/img/searching.drawio.png">
</div>
---

## Searching with ObjectManager

The ObjectManager service provides a dedicated method for searching among objects.

### Properties

| **Parameter** | **Type** | **Description**                                                                       | **Default Value** |
| ------------- | -------- | ------------------------------------------------------------------------------------- | ----------------- |
| `class`       | string   | (required) Class of the objects to search for.                                        |                   |
| `domain`      | array    | Domain (disjunction of conjunctions) defining the criteria the objects have to match. | NULL              |
| `sort`        | array    | Associative array mapping fields and orders on which results have to be sorted.       | ['id' => 'asc']   |
| `start`       | integer  | The offset at which to start the segment of the list of matching objects.             | 0                 |
| `limit`       | integer  | The maximum number of results/identifiers to return.                                  | 0 (no limit)      |
| `lang`        | string   | Language code for localized fields (defaults to DEFAULT_LANG).                        | DEFAULT_LANG      |


**Example**

```php
<?php
$res = $orm->search('core\User', ['login', '=', $login]);
```

---

## Searching with Collections

In controllers, searching can also be invoked through a Collection object, which provides a more fluent interface.

### Properties

| **Parameter** | **Type** | **Description**                                                                       | **Default Value** |
| ------------- | -------- | ------------------------------------------------------------------------------------- | ----------------- |
| `domain`      | array    | Domain (disjunction of conjunctions) defining the criteria the objects have to match. | NULL              |
| `params`      | array    | Associative array of additional parameters (`sort`, `start`, `limit`).                | []                |
| `lang`        | string   | Language code for localized fields (defaults to DEFAULT_LANG).                        | DEFAULT_LANG      |

**Example**

```php
<?php
use core\User;

$collection = User::search([
    ['login', 'like', '%john%'],
    ['validated', '=', 'true']				
]);
```

---

## Access Control for Searching in Collections

Search operations are subject to [access control](../security-access/access-control-lists.md) rules that vary depending on whether the entity implements role-based access control (via a `getRoles()` method) or permission-based access control.

### Create

**Entities with role-based access control:**

* Creation is not supported, as roles relate only to existing objects
* Use [actions](../business-logic/actions.md) (e.g., a 'create' action conditioned by [policies](../security-access/authorization-overview#policies)) to create objects and assign roles like 'owner' to the creating user

**Entities with permission-based access control:**

* Check if the user has the `R_CREATE` right on the entity (via their groups and/or permissions on parent entities)
* Creation fails if the user lacks this right

### Search

**Entities with role-based access control:**

* Identify roles that grant `R_READ` permission (according to the entity's role definitions)
* Modify the search domain to include only objects where the user has one of these roles (via `core_assignment`)
* Add a condition to the domain: `id in [<matching_ids>]`

**Entities with permission-based access control:**

* If the user has `R_READ` permission on the entity (via groups or parent entity rights), perform the search as requested
* If not, list only objects where the user has direct `R_READ` permission and add a condition to the domain: `id in [<matching_ids>]`

### Read, Update, Delete

**Entities with role-based access control:**

* Identify roles that grant `R_READ` permission (according to the entity's role definitions)
* For each object in the result set, verify that the user has at least one of these roles
* The operation fails if the user lacks the required roles on any object (all-or-nothing: either all objects are accessible or the operation is canceled)

**Entities with permission-based access control:**

* If the user has `R_READ` permission on the entity (via groups or parent entity rights), allow the operation
* If not, verify for each object whether the user has direct `R_READ` permission
* The smallest permission level across the collection can be retrieved via `getUserRights()`

---
