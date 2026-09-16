# Programming Paradigm

The eQual framework philosophy focuses on application logic and the systematic description of manipulated entities and their behaviors.

eQual straddles the line between Object-Oriented Programming (OOP) and procedural programming, with a stronger emphasis on entity description rather than pure object-oriented principles.

## Focus on Description

eQual relies on an [Object-Relational Mapping](../entities-persistence/orm.md) (ORM) that implements the Active Record pattern but prioritizes describing relationships and interactions between entities. This ensures the capability to validate, adapt, and export [collections](../entities-persistence/collections/collections-overview.md) of objects.

Unlike traditional OOP, eQual:

- Uses static methods for most operations.
- Describes class members via `getColumns()` instead of directly defining them.
- Manipulates objects in bulk using collections.

This hybrid approach provides flexibility while maintaining simplicity in entity modeling and management.

## Data-Oriented Immutability

eQual complies with principle #3 of the Data-Oriented Programming paradigm: **treat data as immutable**.

This does not mean that persistent records never change. It means that data returned by a read operation is treated as an input value or snapshot. Mutating a returned PHP array or object does not implicitly persist a change. Persistent state changes are requested explicitly through operations such as `create()`, `update()`, `write()` or a named entity action.

This principle also shapes ORM callbacks. A callback receives execution context, identifiers and, when requested in its signature, a collection in `$self`; it must explicitly read the fields required by its computation. It must not rely on an implicitly populated mutable entity. See the [collection data contract](../entities-persistence/collections/collections-overview.md#immutable-data-flow) and the [callback data contract](../entities-persistence/computed-fields.md#callback-data-contract).

## Key Differences from Traditional OOP

| Traditional OOP                           | eQual Approach                               |
| ----------------------------------------- | -------------------------------------------- |
| Classes inherit from various base classes | All classes inherit from `\equal\orm\Model`  |
| Members defined directly in class         | Members described using `getColumns()`       |
| Instance methods using `$this`            | Mostly static methods                        |
| Direct object modification                | Use `create`, `update`, and `delete` methods |
| Single object manipulation                | Bulk manipulation using [collections](../entities-persistence/collections/collections-overview.md)  |

## Terminology

eQual uses specific terminology that differs from traditional OOP:

| OOP Term | eQual Term        |
| -------- | ----------------- |
| Class    | Entity            |
| Object   | Instance          |
| Member   | Property / Column |

eQual [entities](../entities-persistence/entities.md) can be manipulated as PHP objects (`stdClass`) or plain PHP arrays.

## Example: Working with Entities

Here's an example of how to retrieve and manipulate entities:

```php
<?php
use core\User;

$users = User::search()->read(['id', 'name']);
foreach ($users as $id => $user) {
    echo $user->id;
    echo PHP_EOL;
    echo $user['name'];
}
```

In this example:

- `User::search()` retrieves a collection of `User` entities.
- `read(['id', 'name'])` specifies the columns to fetch.
- Each entity can be accessed as an object (`$user->id`) or an array (`$user['name']`).

---
