# Fields

## Field Categories

| **Category** | **Description**                                                                                                             |
| ------------ | --------------------------------------------------------------------------------------------------------------------------- |
| virtual      | Field not stored in the database.                                                                                           |
| simple       | Direct field holding a value of a given type. Stored as-is in the database and automatically converted between SQL and PHP. |
| relational   | Field whose value targets one or more objects. Supported relations: `one2many`, `many2many`, and `many2one`.                |
| computed     | Indirect field resulting from a computation based on other values. See [Computed Fields](computed-fields.md) for details.   |

---

## Field Types and Attributes

The `type` property sets the field type. The following types are supported by the ORM (Object-Relational Mapping). Each type has a set of valid attributes, as enforced by the ORM.

### Common Attributes

Most field types support these attributes:

| **Attribute** | **Description**                                                                 |
| ------------- | ------------------------------------------------------------------------------- |
| type          | The field type (see below). Required for all fields.                            |
| description   | Brief description of the field (max 65 characters).                             |
| help          | Additional help text for the field.                                             |
| visible       | [Domain](../domains.md) holding conditions for field visibility in UI.             |
| default       | Default value or callable returning the default.                                |
| usage         | Additional format information (see [Usages](#usages)).                          |
| dependents    | List of computed fields to reset when this field is updated.                    |
| readonly      | Marks the field as non-editable (default: `false`).                             |
| required      | Marks the field as mandatory (default: `false`).                                |
| deprecated    | Marks the field as deprecated.                                                  |
| onupdate      | Method to invoke when field is updated.                                         |
| multilang     | Marks the field as [translatable](../../i18n/i18n-overview.md) (default: `false`).                     |
| domain        | Additional conditions for relational field targets (see [Domains](../domains.md)). |

---

### Scalar Types

#### boolean

| **Attribute** | **Description**                                              |
| ------------- | ------------------------------------------------------------ |
| type          | (*mandatory*) Must be `'boolean'`.                           |
| description   | Field description.                                           |
| help          | Help text.                                                   |
| visible       | UI visibility.                                               |
| default       | Default value.                                               |
| dependents    | List of computed fields to reset when this field is updated. |
| readonly      | Non-editable.                                                |
| usage         | Format information.                                          |
| required      | Mandatory field.                                             |
| deprecated    | Deprecated field.                                            |
| onupdate      | Method to call on update.                                    |

!!! note "Boolean Notation"
    Use PHP built-in constants `true` and `false`. When using `'0'`, `'1'`, `0`, or `1`, the value is automatically converted to a boolean.

#### integer

| **Attribute** | **Description**                                              |
| ------------- | ------------------------------------------------------------ |
| type          | (*mandatory*) Must be `'integer'`.                           |
| description   | Field description.                                           |
| help          | Help text.                                                   |
| visible       | UI visibility.                                               |
| default       | Default value.                                               |
| dependents    | List of computed fields to reset when this field is updated. |
| readonly      | Non-editable.                                                |
| usage         | Format information.                                          |
| required      | Mandatory field.                                             |
| deprecated    | Deprecated field.                                            |
| onupdate      | Method to call on update.                                    |
| selection     | Pre-defined list of possible values.                         |
| unique        | Enforce uniqueness.                                          |
| precision     | Numeric precision.                                           |

Signed numeric value (negative or positive). By default, integers are stored in the DBMS (Database Management System) using `INT(11)`.

!!! note "Integer Precision"
    PHP integer size depends on the platform (`PHP_INT_SIZE`), while SQL `INT` is always 32 bits.

#### float

| **Attribute** | **Description**                                              |
| ------------- | ------------------------------------------------------------ |
| type          | (*mandatory*) Must be `'float'`.                             |
| description   | Field description.                                           |
| help          | Help text.                                                   |
| visible       | UI visibility.                                               |
| default       | Default value.                                               |
| dependents    | List of computed fields to reset when this field is updated. |
| readonly      | Non-editable.                                                |
| usage         | Format information.                                          |
| required      | Mandatory field.                                             |
| deprecated    | Deprecated field.                                            |
| onupdate      | Method to call on update.                                    |
| selection     | Pre-defined list of possible values.                         |
| precision     | Numeric precision.                                           |

Floating-point numeric value. By default, floats are stored using `DECIMAL(10,2)`.

#### string

| **Attribute** | **Description**                                              |
| ------------- | ------------------------------------------------------------ |
| type          | (*mandatory*) Must be `'string'`.                            |
| description   | Field description.                                           |
| help          | Help text.                                                   |
| visible       | UI visibility.                                               |
| default       | Default value.                                               |
| dependents    | List of computed fields to reset when this field is updated. |
| readonly      | Non-editable.                                                |
| usage         | Format information.                                          |
| required      | Mandatory field.                                             |
| deprecated    | Deprecated field.                                            |
| onupdate      | Method to call on update.                                    |
| multilang     | Translatable field.                                          |
| selection     | Pre-defined list of possible values.                         |
| unique        | Enforce uniqueness.                                          |

Short string without formatting or carriage returns (e.g., lastname, place). By default, stored using `VARCHAR(255)`.

Strings can be longer and include formatting when combined with specific [usages](#usages):

```php
<?php
// Long text stored as MEDIUMTEXT, displayed as TEXTAREA
'description' => [
    'type'  => 'string',
    'usage' => 'text/plain'
]
```

```php
<?php
// Phone number stored as VARCHAR(20)
'phone' => [
    'type'  => 'string',
    'usage' => 'phone'
]
```

!!! note "Key-Value Mapping"
    In associative arrays, avoid numeric keys as they may be interpreted as array indices when converted to JSON, potentially mixing up values and labels.

#### text

| **Attribute** | **Description**                                              |
| ------------- | ------------------------------------------------------------ |
| type          | (*mandatory*) Must be `'text'`.                              |
| description   | Field description.                                           |
| help          | Help text.                                                   |
| visible       | UI visibility.                                               |
| default       | Default value.                                               |
| dependents    | List of computed fields to reset when this field is updated. |
| readonly      | Non-editable.                                                |
| usage         | Format information.                                          |
| required      | Mandatory field.                                             |
| deprecated    | Deprecated field.                                            |
| onupdate      | Method to call on update.                                    |
| multilang     | Translatable field.                                          |

#### date, time, datetime

| **Attribute** | **Description**                                              |
| ------------- | ------------------------------------------------------------ |
| type          | (*mandatory*) Must be `'date'`, `'time'`, or `'datetime'`.   |
| description   | Field description.                                           |
| help          | Help text.                                                   |
| visible       | UI visibility.                                               |
| default       | Default value.                                               |
| dependents    | List of computed fields to reset when this field is updated. |
| readonly      | Non-editable.                                                |
| usage         | Format information.                                          |
| required      | Mandatory field.                                             |
| deprecated    | Deprecated field.                                            |
| onupdate      | Method to call on update.                                    |

When stored in the DBMS, these types follow standard SQL formats:

- **date**: `YYYY-mm-dd`
- **time**: `HH:mm:ss`
- **datetime**: `YYYY-mm-dd HH:mm:ss`

!!! tip "Datetime Manipulations"
    Within PHP scripts, eQual handles `date`, `time`, and `datetime` as UTC timestamps. These are adapted to SQL or JSON format when needed.

#### file, binary

| **Attribute** | **Description**                                              |
| ------------- | ------------------------------------------------------------ |
| type          | (*mandatory*) Must be `'file'` or `'binary'`.                |
| description   | Field description.                                           |
| help          | Help text.                                                   |
| visible       | UI visibility.                                               |
| default       | Default value.                                               |
| dependents    | List of computed fields to reset when this field is updated. |
| readonly      | Non-editable.                                                |
| usage         | Format information.                                          |
| required      | Mandatory field.                                             |
| deprecated    | Deprecated field.                                            |
| onupdate      | Method to call on update.                                    |
| multilang     | Translatable field.                                          |

Any binary value (e.g., pictures, documents). Binary values can be stored either in the database or within the filesystem under the `/bin` folder. This behavior is controlled by the `FILE_STORAGE_MODE` and `FILE_STORAGE_DIR` configuration parameters.

A file value stored in the database as `LONGBLOB`.

---

### Relational Types

#### many2one

| **Attribute**  | **Description**                                              |
| -------------- | ------------------------------------------------------------ |
| type           | (*mandatory*) Must be `'many2one'`.                          |
| description    | Field description.                                           |
| help           | Help text.                                                   |
| visible        | UI visibility.                                               |
| default        | Default value.                                               |
| dependents     | List of computed fields to reset when this field is updated. |
| readonly       | Non-editable.                                                |
| required       | Mandatory field.                                             |
| deprecated     | Deprecated field.                                            |
| foreign_object | (*mandatory*) Full name of the target class.                 |
| domain         | Additional conditions for relational field targets.          |
| onupdate       | Method to call on update.                                    |
| ondelete       | Behavior when parent is deleted: `'null'` or `'cascade'`.    |
| multilang      | Translatable field.                                          |

Relational field for N-1 relationships. The stored value is an integer holding the identifier of the pointed object.

```php
<?php
// Each task has only one user assigned at a time
'user_id' => [
    'type'           => 'many2one',
    'foreign_object' => 'todolist\User'
]
```

#### one2many

| **Attribute**  | **Description**                                                 |
| -------------- | --------------------------------------------------------------- |
| type           | (*mandatory*) Must be `'one2many'`.                             |
| description    | Field description.                                              |
| help           | Help text.                                                      |
| visible        | UI visibility.                                                  |
| default        | Default value.                                                  |
| dependents     | List of computed fields to reset when this field is updated.    |
| readonly       | Non-editable.                                                   |
| deprecated     | Deprecated field.                                               |
| foreign_object | (*mandatory*) Full name of the target class.                    |
| foreign_field  | (*mandatory*) Name of the field pointing back to current class. |
| domain         | Additional conditions for relational field targets.             |
| onupdate       | Method to call on update.                                       |
| ondetach       | Behavior when relation is removed: `'null'` or `'delete'`.      |
| order          | Field to sort pointed objects on.                               |
| sort           | Sort direction: `'asc'` or `'desc'`.                            |

Relational field for 1-N relationships, linked to a `many2one` field on the related object.

```php
<?php
// Each user can have many tasks
'tasks_ids' => [
    'type'           => 'one2many',
    'foreign_object' => 'todolist\Task',
    'foreign_field'  => 'user_id'
]
```

#### many2many

| **Attribute**   | **Description**                                                      |
| --------------- | -------------------------------------------------------------------- |
| type            | (*mandatory*) Must be `'many2many'`.                                 |
| description     | Field description.                                                   |
| help            | Help text.                                                           |
| visible         | UI visibility.                                                       |
| default         | Default value.                                                       |
| dependents      | List of computed fields to reset when this field is updated.         |
| readonly        | Non-editable.                                                        |
| deprecated      | Deprecated field.                                                    |
| foreign_object  | (*mandatory*) Full name of the target class.                         |
| foreign_field   | (*mandatory*) Name of the field pointing back to current class.      |
| rel_table       | (*mandatory*) Name of the pivot table.                               |
| rel_local_key   | (*mandatory*) Column in pivot table for current object's identifier. |
| rel_foreign_key | (*mandatory*) Column in pivot table for target object's identifier.  |
| domain          | Additional conditions for relational field targets.                  |
| onupdate        | Method to call on update.                                            |

Relational field for M-N relationships, requiring a pivot table.

```php
<?php
// Teachers can teach multiple courses, courses can have multiple teachers
'courses_ids' => [
    'type'            => 'many2many', 
    'foreign_object'  => 'school\Course', 
    'foreign_field'   => 'teachers_ids', 
    'rel_table'       => 'school_rel_course_teacher', 
    'rel_foreign_key' => 'course_id', 
    'rel_local_key'   => 'teacher_id'
]
```

---

### Virtual Types

#### alias

| **Attribute** | **Description**                                       |
| ------------- | ----------------------------------------------------- |
| type          | (*mandatory*) Must be `'alias'`.                      |
| description   | Field description.                                    |
| help          | Help text.                                            |
| visible       | UI visibility.                                        |
| default       | Default value.                                        |
| usage         | Format information.                                   |
| alias         | (*mandatory*) Name of the field this is an alias for. |
| required      | Mandatory field.                                      |
| deprecated    | Deprecated field.                                     |

Targets another field whose value is returned when fetching the field. By default, the `name` field is an alias for `id`.

```php
<?php
'display_name' => [
    'type'  => 'alias',
    'alias' => 'name'
]
```

#### computed

| **Attribute**  | **Description**                                              |
| -------------- | ------------------------------------------------------------ |
| type           | (*mandatory*) Must be `'computed'`.                          |
| description    | Field description.                                           |
| help           | Help text.                                                   |
| visible        | UI visibility.                                               |
| default        | Default value.                                               |
| dependents     | List of computed fields to reset when this field is updated. |
| readonly       | Non-editable.                                                |
| deprecated     | Deprecated field.                                            |
| result_type    | (*mandatory*) Type of the computed result.                   |
| usage          | Format information.                                          |
| function       | (*mandatory*) Callable for computation.                      |
| relation       | (*mandatory*) Relation information.                          |
| onupdate       | Method to call on update.                                    |
| onrevert       | Method to call on revert.                                    |
| store          | Whether the computed value is stored.                        |
| instant        | Whether the value is computed instantly.                     |
| multilang      | Translatable field.                                          |
| domain         | Additional conditions for relational field targets.          |
| selection      | Pre-defined list of possible values.                         |
| foreign_object | Target class name (if applicable).                           |

See [Computed Fields](computed-fields.md) for detailed documentation.

---

## Usages

The `usage` property is complementary to `type` and refines how the field is handled by the ORM, DBMS, and UI.

### Purpose

- **Storage allocation**: Determines appropriate database column types
- **Format validation**: Ensures data conforms to expected patterns
- **UI rendering**: Guides how fields are displayed and edited

### Common Usages

The `usage` property refines how a field is stored, validated, and rendered. Usages are written as `content-type/subtype[:precision[.scale]]`.

| **Usage**                   | **Description**                                                                                                                                   | **Example**                   |
| --------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------- |
| `text/plain`                | Plain text, long or short                                                                                                                         | `text/plain:65000`            |
| `text/html`                 | HTML formatted content                                                                                                                            | `text/html`                   |
| `text/json`                 | JSON formatted content                                                                                                                            | `text/json`                   |
| `text/xml`                  | XML formatted content                                                                                                                             | `text/xml`                    |
| `amount`                    | Numeric amount (supports +, -, decimals)                                                                                                          | `amount:10.2`                 |
| `amount/money`              | Monetary value, with decimals                                                                                                                     | `amount/money:2`              |
| `amount/percent`            | Percentage value                                                                                                                                  | `amount/percent`              |
| `amount/rate`               | Rate (e.g., tax)                                                                                                                                  | `amount/rate`                 |
| `array`                     | List of values                                                                                                                                    | `array`                       |
| `binary`                    | Binary data (files, images, etc.)                                                                                                                 | `binary`                      |
| `country/numeric`           | Numeric country code ([ISO-3166](https://www.iso.org/iso-3166-country-codes.html))                                                                | `country/numeric:3`           |
| `currency/iso-4217.numeric` | Numeric currency code ([ISO-4217](https://www.iso.org/iso-4217-currency-codes.html))                                                              | `currency/iso-4217.numeric:3` |
| `email`                     | Email address                                                                                                                                     | `email`                       |
| `language`                  | Language name or code ([iso-639-2 alpha-3](https://en.wikipedia.org/wiki/ISO_639-2)/[iso-639-1 alpha-2](https://en.wikipedia.org/wiki/ISO_639-1)) | `language:2`                  |
| `locale`                    | Locale code (`lang[_COUNTRY]` or `lang`)                                                                                                          | `locale:aa`                   |
| `number/boolean`            | Boolean value                                                                                                                                     | `number/boolean`              |
| `number/integer`            | Integer value                                                                                                                                     | `number/integer:9`            |
| `number/natural`            | Natural number (positive integer)                                                                                                                 | `number/natural`              |
| `number/real`               | Decimal number                                                                                                                                    | `number/real:5.2`             |
| `password/enisa`            | ENISA-compliant password                                                                                                                          | `password/enisa`              |
| `password/nist`             | NIST-compliant password                                                                                                                           | `password/nist`               |
| `phone`                     | Phone number (normalized format)                                                                                                                  | `phone`                       |
| `date/plain`                | Date (YYYY-MM-DD)                                                                                                                                 | `date/plain`                  |
| `date/time`                 | DateTime (YYYY-MM-DD HH:MM:SS)                                                                                                                    | `date/time`                   |
| `date/day`                  | Day of month (1-31)                                                                                                                               | `date/day`                    |
| `date/month`                | Month (1-12)                                                                                                                                      | `date/month`                  |
| `date/year`                 | Year (4 digits)                                                                                                                                   | `date/year:4`                 |
| `date/yearweek`             | Year-week ([ISO-8601](https://www.iso.org/iso-8601-date-and-time-format.html))                                                                                                                      | `date/yearweek`               |
| `date/yearday`              | Day of year (1-366)                                                                                                                               | `date/yearday`                |
| `date/weekday.mon`          | Weekday (1=Monday, 7=Sunday, [ISO-8601](https://www.iso.org/iso-8601-date-and-time-format.html))                                                                                                    | `date/weekday.mon`            |
| `date/weekday.sun`          | Weekday (0=Sunday, 6=Saturday)                                                                                                                    | `date/weekday.sun`            |
| `time/plain`                | Time (HH:MM:SS)                                                                                                                                   | `time/plain`                  |
| `uri/url`                   | URL                                                                                                                                               | `uri/url`                     |
| `uri/url.relative`          | Relative URL                                                                                                                                      | `uri/url.relative`            |
| `uri/url.tel`               | Telephone URL                                                                                                                                     | `uri/url.tel`                 |
| `uri/url.mailto`            | Mailto URL                                                                                                                                        | `uri/url.mailto`              |
| `uri/urn.iban`              | IBAN number                                                                                                                                       | `uri/urn.iban`                |
| `uri/urn.ean`               | EAN barcode                                                                                                                                       | `uri/urn.ean`                 |
| `image/jpeg`                | JPEG image                                                                                                                                        | `image/jpeg`                  |

**Notes:**

- Deprecated: Use `binary` instead of `file` for file storage.
- For `email`, only standard email formats are accepted:

*supports:*

     -            c@a.com
     -            c@a.b.com
     -            c@a.b.c.com
     -            a+c@a.b.c.com
     -            a.c+d@a.b.c.com
     -            ad@a.b.c.xn--vermgensberatung-pwb
*does not support:*

     -            a+c.d@a.b.c.com

- For `phone`, only normalized formats are accepted (e.g., `+32478456789`, `0032478456789`, `0478456789`).
- For `password/nist`, must be min. 8 chars, 1 digit, 1 uppercase, 1 lowercase, 1 special char (`#?!@$%^*-`).
- For `locale`, allowed formats: `{iso-639}` or `{iso-639}_{iso-3166}` (e.g., `fr`, `fr_BE`).

**Syntax:**  
Usages follow this pattern:  
```
content-type/subtype[:precision[.scale]]
```
Example: `number/real:10.2` (decimal with 10 digits, 2 decimals)

### Type to Content-Type Mapping

| **Type**  | **Content-Type** |
| --------- | ---------------- |
| boolean   | `number/boolean` |
| integer   | `number/integer` |
| float     | `number/real`    |
| string    | `text/plain`     |
| date      | `date/plain`     |
| datetime  | `date/datetime`  |
| time      | `time/plain`     |
| binary    | `binary/plain`   |
| many2one  | `number/natural` |
| one2many  | `array`          |
| many2many | `array`          |

### Constraints and Validation

The `Field` class provides a `getConstraints()` method to retrieve constraints specific to the field's type and usage. When values are associated with a usage, their validity is checked via the usage's constraint definitions.

### Syntax

Usages follow this general pattern:
```
content-type/subtype:precision.scale
```

---

## Data Adaptation

Objects manipulated in controllers and classes contain values using PHP types. Collections can be exported by recursively converting values during export using:

```php
$adapter->adaptOut($value, $field->getUsage())

```

This ensures proper format conversion between PHP, SQL, and JSON representations.

---
