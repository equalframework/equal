# Validation

Validation ensures that data provided by users matches the field requirements defined in object schemas. The ObjectManager provider's `validate()` function performs this check during CREATE and UPDATE operations, as well as when [controllers](../controllers-routing/controllers.md) are invoked with parameters.

The eQual framework treats both Models and Controllers as entities—their fields (provided by `getColumns()` and `params` respectively) are validated using the same logic.

---

## Validation Mechanism

Field validation is based on two complementary concepts: **Type** and **Usage**.

* **Type** defines how the field is stored in the database (e.g., `string`, `integer`, `date`)
* **Usage** refines how the field should be handled by the ORM, database, and UI (e.g., `email`, `phone`, `currency`)
* **Constraints** define specific validation rules that enforce data integrity

### Type and Usage Relationship

Every field has a [usage](../models/entities/fields.md#usages) assigned. If not explicitly defined, it defaults based on the type. Usage takes precedence during validation—the type and usage must be compatible.

**Example:**

```php
<?php
'params' => [
    'code' => [
        'description'   => 'Code for authenticating user.',
        'type'          => 'string',
        'usage'         => 'email',
        'required'      => true
    ]
]
```

Here, the type is `string`, and the usage specifies that the string should be a valid email address.

---

## Constraint Types

Constraints are either **explicit** (defined via Usage) or **implicit** (derived from the Type).

However, the way their constraints are checked is the same: each constraint function is called with the value. If any constraint fails, its error code and message are collected and returned:

```php
[
    'message' => 'Descriptive error message if validation fails.',
    'function' => function($value, $object) { /* validation logic */ }
]
```

Usages define validation rules tailored to specific data formats. The `getConstraints(Field $field)` method in `DataValidator.class.php` returns constraints for field type based on usage. Therefore, the same field type can have different constraints depending on its usage. For full details on available usages and their constraints, refer to the [Usage documentation](../models/entities/fields.md#usages).

**Example with Email Usage:**

```php
<?php
    public function getConstraints(): array {
        return [
            'invalid_email' => [
                'message'   => 'Malformed email address.',
                'function'  =>  function($value) {
                    return (bool) (preg_match('/^([_a-z0-9-\.]+)(\+([_a-z0-9]+))?@(([a-z0-9-]+\.)*)([a-z0-9-]{1,63})(\.[a-z-]{2,24})$/i', $value));
                }
            ]
        ];
    }
```

The rule validates that the field is a properly formatted email address using regex. If validation fails, returns `false`.

### Handled Constraints Types

**Custom Function Constraints**: 

* Any function that returns a `boolean`.

**Pattern Constraints**:

* If the field descriptor has a `pattern` property, the value must match the provided regular expression.

**Selection Constraints**:

* If the field descriptor has a `selection` property, the value must be one of the allowed options.


---

## Validation Rules

### Required Fields

The `required` property indicates that a field must be provided and cannot be null:

```php
<?php
foreach($schema as $field => $def) {
    // required fields must be provided and cannot be left/set to null
    if( isset($def['required']) && $def['required'] && (!isset($values[$field]) 
    || is_null($values[$field])) ) {
        $error_code = EQ_ERROR_INVALID_PARAM;
        $res[$field]['missing_mandatory'] = 'Missing mandatory value.'; 
        trigger_error("EQ_DEBUG_ORM::mandatory field {$field} is missing for 
        instance of {$class}", EQ_REPORT_WARNING);
    }
}
```

If a required field is missing or null, validation fails with a `missing_mandatory` error.

### Unique Constraints via `getUnique()`

The `getUnique()` method provides a list of unique constraint rules—combinations of fields that must be unique across instances.

If a unique constraint is violated:

```php
<?php
 trigger_error("EQ_DEBUG_ORM::field {$field} violates unique constraint with 
 object {$oid}", EQ_REPORT_WARNING);

```

---

## Validation Error Response

When validation fails, the response includes detailed error information structured by field and constraint type.

### Invalid Parameter Errors

When a controller is invoked with parameters that fail validation:

```json
{
    "errors": {
        "INVALID_PARAM": {
            "id": {
                "broken_constraint": "Invalid parameter"
            },
            "date": {
                "broken_constraint": "Invalid parameter"
            },
            "legal_name": {
                "invalid_chars": "Legal name must contain only naming glyphs.",
                "too_short": "Legal name must be minimum 2 characters long."
            }
        }
    }
}
```

Each field lists the constraint(s) that failed along with a descriptive message.

### Missing Required Parameter

When a required parameter is missing:

```json
{
    "errors": {
        "MISSING_PARAM": "id"
    }
}
```

### Controller Announcements

When a controller raises a validation error and calls the `eQual::announce()` method, the response includes an `announcement` property describing the expected request format:

```json
{
    "announcement": {
        "description": "Generates a PDF version of a Bonus Report.",
        "params": {
            "id": {
                "description": "Identifier of the report to print.",
                "type": "integer",
                "required": true
            }
        }
    },
    "errors": {
        "MISSING_PARAM": "id"
    }
}
```

---