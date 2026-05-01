# Computed Fields

Computed fields allow you to dynamically derive values based on other data in the model. These values can either be computed **synchronously at runtime** (when the field is accessed) or **precomputed and stored in the database**.

---

## When to Use Stored Computed Fields

Storing the value in the database is necessary when the field needs to be:

- Used in [domains](domains.md) or filtering conditions
- Indexed for performance
- Queried without triggering computation

To store computed values, set:

```php
'store' => true
```

## Defining Computed Fields

### Using `function`

A callable used to compute the value. It can be:

**A string referencing a method name:**

```php
<?php
'rights_txt' => [
    'type'        => 'computed', 
    'store'       => true, 
    'result_type' => 'string', 
    'function'    => 'calcRightsTxt'
]
```

**An anonymous function (closure):**

```php
<?php
'timestamp' => [
    'type'        => 'computed',
    'result_type' => 'integer',
    'function'    => function() { return time(); }
]
```

It is **strongly recommended** to define named methods with **`protected` scope** so they:

- Remain **inaccessible from outside** the class, preserving encapsulation
- Are still **available to child classes**, enabling controlled inheritance

### Using `relation`

An associative array describing a **path of relations** to follow to retrieve the value from a related entity:

```php
<?php
'vat_rate' => [
    'type'        => 'computed',
    'result_type' => 'float',
    'relation'    => ['sale_entry_id' => 'vat_rate']
]
```

This follows the relation to `sale_entry_id`, then retrieves the `vat_rate` field from that related object.

---

## Properties

| **Attribute**  | **Type**                | **Description**                                                                                    |
| -------------- | ----------------------- | -------------------------------------------------------------------------------------------------- |
| type           | `string`                | (required)Type of the computed result (`boolean`, `integer`, `float`, `string`, `many2one`, etc.). |
| description    | `string`                | Field description.                                                                                 |
| help           | `string`                | Help text.                                                                                         |
| visible        | `boolean`               | UI visibility.                                                                                     |
| default        | `mixed`                 | Default value.                                                                                     |
| dependents     | `array`                 | List of computed fields to reset when this field is updated.                                       |
| readonly       | `boolean`               | Non-editable.                                                                                      |
| deprecated     | `boolean`               | Deprecated field.                                                                                  |
| result_type    | `string`                | (required) Type of the computed result.                                                            |
| usage          | `string`                | Format information.                                                                                |
| function       | `string` \| `callable`  | (required) Method name or closure for computing the value.                                         |
| relation       | `array`                 | (required) Path of relations to follow for retrieving the value.                                   |
| onupdate       | `string`                | Method to call on update.                                                                          |
| onrevert       | `string`                | Method to invoke when the field is reverted to `NULL`.                                             |
| store          | `boolean`               | Whether the computed value is stored (default: `false`).                                           |
| instant        | `boolean`               | Whether the value is computed instantly.                                                           |
| multilang      | `boolean`               | Whether the field is translatable (only for stored fields).                                        |
| domain         | `array`                 | Additional conditions for relational field targets.                                                |
| selection      | `array`                 | Pre-defined list of possible values.                                                               |
| foreign_object | `string`                | Target class name (if applicable).                                                                 |

## Computation Behavior

When loading a computed field:

1. If `store` is not defined or `false`: computes the value using the provided method each time
2. If `store` is `true` and the field is `NULL` in database: computes and stores the value
3. If `store` is `true` and the field has a database value: uses the stored value

## Updating Stored Computed Fields

When using the `store` attribute, you must typically define an **`onchange` event** (or use the `dependents` property) on the field(s) that influence the computed value. This ensures the computed field is recalculated when its dependencies change.

### Using `dependents`

The `dependents` property lists computed fields that should be reset when a field is updated:

```php
<?php
'rights' => [
    'type'       => 'integer',
    'dependents' => ['rights_txt']
],
'rights_txt' => [
    'type'        => 'computed', 
    'store'       => true, 
    'result_type' => 'string', 
    'function'    => 'calcRightsTxt'
]
```

## Complete Example

```php
<?php
namespace core;

use equal\orm\Model;

class Permission extends Model {

    public static function getColumns() {
        return [
            'rights' => [
                'type'       => 'integer',
                'dependents' => ['rights_txt']
            ],
            'rights_txt' => [
                'type'        => 'computed', 
                'store'       => true, 
                'result_type' => 'string', 
                'function'    => 'calcRightsTxt'
            ]
        ];
    }

    protected static function calcRightsTxt($self) {
        $result = [];
        $values = $self->read(['rights']);
        
        foreach($values as $id => $value) {
            $rights_txt = [];
            $rights = $value['rights'];
            
            if($rights & EQ_R_CREATE) $rights_txt[] = 'create';
            if($rights & EQ_R_READ)   $rights_txt[] = 'read';
            if($rights & EQ_R_WRITE)  $rights_txt[] = 'write';
            if($rights & EQ_R_DELETE) $rights_txt[] = 'delete';
            if($rights & EQ_R_MANAGE) $rights_txt[] = 'manage';
            
            $result[$id] = implode(', ', $rights_txt);
        }
        
        return $result;
    }
}
```

---

## Best Practices

!!! warning "Avoid Calling `update()` in Callbacks"
    Within computed field callbacks, you should never call `update()` on a collection involving the current entity (`self`). This would mark the object as an instance even if it is still a draft.

**Method Scope Guidelines:**

- Use `protected` scope for computation methods to allow inheritance while preventing external access
- Use meaningful method names that reflect the computation purpose (e.g., `calcRightsTxt`, `computeTotal`)
- Keep computation logic focused and avoid side effects

---

