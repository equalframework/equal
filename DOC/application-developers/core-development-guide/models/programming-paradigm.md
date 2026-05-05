# Programming Paradigm

The eQual framework philosophy focuses on application logic and the systematic description of manipulated entities and their behaviors.

eQual straddles the line between Object-Oriented Programming (OOP) and procedural programming, with a stronger emphasis on entity description rather than pure object-oriented principles.

## Focus on Description

eQual relies on an [Object-Relational Mapping](./orm.md) (ORM) that implements the Active Record pattern but prioritizes describing relationships and interactions between entities. This ensures the capability to validate, adapt, and export [collections](./collections/collections-overview.md) of objects.

Unlike traditional OOP, eQual:

- Uses static methods for most operations.
- Describes class members via `getColumns()` instead of directly defining them.
- Manipulates objects in bulk using collections.

This hybrid approach provides flexibility while maintaining simplicity in entity modeling and management.

## Key Differences from Traditional OOP

| Traditional OOP                           | eQual Approach                               |
| ----------------------------------------- | -------------------------------------------- |
| Classes inherit from various base classes | All classes inherit from `\equal\orm\Model`  |
| Members defined directly in class         | Members described using `getColumns()`       |
| Instance methods using `$this`            | Mostly static methods                        |
| Direct object modification                | Use `create`, `update`, and `delete` methods |
| Single object manipulation                | Bulk manipulation using [collections](./collections/collections-overview.md)  |

## Terminology

eQual uses specific terminology that differs from traditional OOP:

| OOP Term | eQual Term        |
| -------- | ----------------- |
| Class    | Entity            |
| Object   | Instance          |
| Member   | Property / Column |

eQual [entities](./entities/entities-overview.md) can be manipulated as PHP objects (`stdClass`) or plain PHP arrays.

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