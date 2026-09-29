# ORM reference

Use this reference for `classes/*.class.php`, ORM hooks, synchronization logic, computed fields, model actions, and collection semantics.

Key rules:

- Prefer existing package model patterns before introducing new abstractions.
- Use `getColumns()` for fields and keep field metadata coherent with nearby entities.
- Avoid side effects in computed fields, especially `update()` calls.
- For synchronization logic, prefer a named ORM action in `getActions()` and trigger it with `$self->do('action_name')` from hooks.
- Avoid new public helper methods unless a stable existing consumer or cross-entity API requires them.
- Preserve policy, error-key, hook, and action behavior when changing existing models.
- After changing `packages/{package}/classes/*.class.php`, check DB access and reinitialize the impacted package as described in `AGENTS.md`.

## Relation values after `read()`

Never infer the runtime type of a relational value from `many2one`, `one2many`, or `many2many` alone. Before using it, inspect both:

1. the exact projection passed to `read()`;
2. whether the collection is subsequently converted to a PHP array.

### Without relational subfields

```php
$self->read([
    'identity_id',
    'identities_ids'
]);
```

The loaded values are identifiers:

| Relation | Runtime value |
| --- | --- |
| `many2one` | `int|null` |
| `one2many` or `many2many` | `int[]` |

Use PHP array functions for to-many values:

```php
if(empty($object['identities_ids'])) {
    // ...
}

$removed_ids = array_map(
    fn($id) => -$id,
    $object['identities_ids']
);
```

Do not call `count()`, `ids()`, or `first()` as methods on these values.

### With relational subfields

```php
$self->read([
    'identity_id'   => ['email'],
    'identities_ids' => ['email']
]);
```

The loaded values are ORM objects:

| Relation | Runtime value |
| --- | --- |
| `many2one` | related `Model` or `null` |
| `one2many` or `many2many` | `Collection` of related models |

Use the ORM object and collection APIs:

```php
if($object['identities_ids']->count() > 0) {
    $identity = $object['identities_ids']->first();
}

foreach($object['identities_ids'] as $identity) {
    $email = $identity['email'];
}

$identity_ids = $object['identities_ids']->ids();
```

The same rule applies at every nesting level. Do not pass a `Collection` directly to `empty()`, `reset()`, `array_map()`, or `array_filter()`.

### Mixed and nested projections

A single projection can deliberately produce both collections and arrays of IDs:

```php
$self->read([
    'ownerships_ids' => [
        'communication_preferences_ids' => [
            'is_owner',
            'identity_id'
        ]
    ],
    'owners_ids' => ['identity_id'],
    'identities_ids'
]);
```

In this example:

- `ownerships_ids` is a `Collection`;
- each `communication_preferences_ids` value is a `Collection`;
- `owners_ids` is a `Collection`;
- each projected `identity_id` has no subfields of its own, so it is `int|null`;
- the top-level `identities_ids` has no subfields, so it is `int[]`.

Re-evaluate the rule at each relation in the projection. A parent relation being a `Collection` does not make its scalar child relations models or collections.

### Keep collections iterable

`Collection` is iterable. Keep it as a collection and iterate over it directly unless a downstream API explicitly requires a PHP array:

```php
$collection->read([...]);

foreach($collection as $object) {
    // $object is a Model.
}
```

Do not call `get()` merely to iterate over the result. This needlessly discards the ORM object and collection APIs.

### Explicit array conversion

When a PHP array is actually required, use the explicit `toArray()` method. It recursively converts models and nested collections to lists with numeric indexes:

```php
$objects = $collection->read([...])->toArray();
```

`get()` also performs a recursive conversion but preserves collection IDs as associative keys by default. Avoid it in generated code unless an ID-keyed map is an explicit requirement that cannot be met while retaining the `Collection`:

```php
$objects_by_id = $collection->read([...])->get();
```

After conversion, use PHP array functions rather than `Collection` methods.

### Values passed to relational updates

For a `many2many` update, provide an array of identifiers or relation descriptors, never a `Collection` directly. Signed identifiers can express additions and removals where that update contract is used:

```php
[
    12,   // Add the relation.
    -18   // Remove the relation.
]
```

Practical rule:

```text
Relation without subfields -> ID or array of IDs
Relation with subfields    -> Model or Collection
Call to toArray()          -> recursive conversion to PHP arrays
```

For framework ORM internals under `lib/equal/orm/**`, use `AGENTS/70-framework-internals/INSTRUCTIONS.md`.
