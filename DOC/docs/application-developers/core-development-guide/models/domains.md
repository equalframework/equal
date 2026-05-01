# Domains: Overview and Structure

In eQual, **domains** are a powerful tool for defining conditional filters and constraints. They are used extensively for operations such as filtering objects, determining visibility, and evaluating conditions in various contexts.

---

## What is a Domain?

A **Domain** is a structure that defines a set of conditions used to filter objects or evaluate boolean flags based on a given context. Domains are primarily used in:

- **Views**: To control visibility.
- **Models and Controllers**: To filter data.

Domains are represented as a logical structure of conditions, which can be incrementally built and manipulated. The eQual framework provides a `Domain` class to facilitate this process.

---

## Domain Structure

A domain is a list of one or more **clauses**, where each clause consists of one or more **conditions**. The minimal domain is a single clause with a single condition. For examples refer to the [example domain tutorial](../../how-tos-references/tutorials-examples/example-domain.md).

### Condition

A **condition** is represented as an array with three elements:

```php
[ operand, operator, value ]
```

| **Part**   | **Description**                                                                                |
| ---------- | ---------------------------------------------------------------------------------------------- |
| `operand`  | The field on which the condition is applied. It matches a property from `Model::getColumns()`. |
| `operator` | The operator for the condition. Supported operators vary based on the operand type.            |
| `value`    | The value against which the operator is applied.                                               |

> **Note**: The operator determines the type of the value. For example, the `in` operator requires the value to be an array.

#### Supported Operand/Operators

| **Operand Type**                | **Valid Operators**                                                         |
| ------------------------------- | --------------------------------------------------------------------------- |
| `boolean`                       | `=`, `<>`, `<`, `>`, `is`, `in`, `is not`                                   |
| `integer`                       | `=`, `<>`, `<`, `>`, `<=`, `>=`, `in`, `not in`, `is`, `is not`             |
| `float`                         | `=`, `<>`, `<`, `>`, `<=`, `>=`, `in`, `not in`, `is`, `is not`             |
| `string`                        | `=`, `<>`, `like`, `ilike`, `in`, `not in`, `is`, `is not`                  |
| `text`                          | `is`, `is not`                                                              |
| `date`                          | `=`, `<>`, `<`, `>`, `<=`, `>=`, `like`, `is`, `is not`                     |
| `time`                          | `=`, `<>`, `<`, `>`, `<=`, `>=`, `is`, `is not`                             |
| `datetime`                      | `=`, `<>`, `<`, `>`, `<=`, `>=`, `is`, `is not`                             |
| `file` (deprecated, use binary) | `is`, `is not`, `=`, `like`, `ilike`                                        |
| `binary`                        | `is`, `is not`, `=`, `like`, `ilike`                                        |
| `one2many`                      | `contains`                                                                  |
| `many2one`                      | `=`, `<>`, `<`, `>`, `<=`, `>=`, `in`, `not in`, `is`, `is not`, `contains` |
| `many2many`                     | `contains`                                                                  |

> **Note**: The `ilike` operator performs a case-insensitive search, while `like` is case-sensitive. The `contains` operator checks for the presence of a value in a relation.

**Example: Condition**

```php
["field1", "=", 1]
```

This condition checks if `field1` equals `1`.

---

### Clause

A **clause** is a series of conditions combined with the logical `AND` operator.

```php
[
    ["field1", "=", 1],
    ["field2", "=", 2]
]
```

This clause can be interpreted as: "*field1 must equal 1 AND field2 must equal 2*."

---

### Domain Notation

A complete domain is a list of clauses combined with the logical `OR` operator. Each clause contains conditions combined with `AND`.

```php
[
    [ ["field1", "=", 1], ["field2", "=", 2] ], // Clause 1
    [ ["field3", ">", 5] ]                     // Clause 2
]
```

This domain can be interpreted as: "*field1 equals 1 AND field2 equals 2 OR field3 is greater than 5*."

#### Shortcut Notations

eQual supports shortcut notations for simpler domains:

- `[operand, operator, value]`: Interpreted as a domain with one clause and one condition.
- `[[operand, operator, value]]`: Interpreted as a domain with one clause containing one condition.

**Example: Shortcut Notations**

```php
["field1", "=", 1] // Single condition
[[ "field1", "=", 1 ]] // Single clause with one condition
```

---

## Using Domains in Code

Domains can be defined in both PHP and JSON, making them versatile for use in controllers and views.

**PHP Example**
```php
$domain = [
    ["login", "like", "%@equal.run"],
    ["validated", "=", true]
];
```

**JSON Example**
```json
{
    "domain": [
        ["login", "like", "%@equal.run"],
        ["validated", "=", true]
    ]
}
```

**URL Example**

Domains can also be used in URLs (must be URL-encoded):

```http
http://equal.local/?get=model_search&entity=core\User&domain=[[id,in,[1,2,3]]]
```

---

## References in Domains

eQual supports references to dynamic values in domains, such as the current object, user, or date. These references enhance flexibility and allow for context-aware filtering.

### Object Reference

Domains can refer to the current object in a model or view. This is useful for filtering objects based on their relationship to the current object.

**Example: Object Reference**

Let's say we have two entities: `Project` and `Techie`.

* A **Project** depends on the company but is assigned to a specific department.
* One or more **Techies** can be assigned to a project, but only amongst those who belong to the same department as the project.
  
Here is how the domain might look in eQual:
```json
{
    "techies_ids": {
        "type": "one2many",
        "domain": ["department_id", "=", "object.department_id"]
    }
}
```

This domain filters **Techies** to only include those in the same department as the current **Project**.

---

### User Reference

Domains can reference the current user, enabling user-specific filtering. Available fields are:

| **Field**    | **Description**                                     |
| ------------ | --------------------------------------------------- |
| `id`         | The unique identifier of the user.                  |
| `login`      | The login name of the user.                         |
| `validated`  | A boolean indicating whether the user is validated. |
| `language`   | The language preference of the user.                |
| `groups_ids` | A list of group IDs that the user belongs to.       |
| `groups`     | A list of group names that the user belongs to.     |

#### Example: User Reference

```php
["creator", "=", "user.id"]
```

This condition filters objects created by the current user.

---

### Date Reference

eQual provides a structured way to reference dates dynamically. Date references allow filtering based on relative dates, such as the current day, week, or year.

#### Date Reference Syntax

```text
date.<origin>(<offset>).<interval>.<method>(<arguments>)

or

date.{this|prev|next}[(<offset>)].{day|week|month|quarter|semester|year}.{first|last|get(reference:index)}
```

#### 1. Origin

| Method  | Description                                                | Offset | Possible Values   |
| ------- | ---------------------------------------------------------- | ------ | ----------------- |
| prev(n) | First day of previous period with an offset of n intervals | n > 0  | `prev`, `prev(n)` |
| next(n) | First day of next period with an offset of n intervals     | n > 0  | `next`, `next(n)` |
| this()  | First day of current period                                | n = 0  | `this`, `this(0)` |

#### 2. Interval

| Interval | Description |
| -------- | ----------- |
| day      | Day         |
| week     | Week        |
| month    | Month       |
| quarter  | Quarter     |
| semester | Semester    |
| year     | Year        |

#### 3. Method

| Method  | Description                        |
| ------- | ---------------------------------- |
| first() | First day of the interval          |
| last()  | Last day of the interval           |
| get()   | Get a specific date with arguments |


##### Arguments for the `get()` Method

| Argument (reference:index) | Concerned Interval             | Possible Values                                                                                                                                                      |
| -------------------------- | ------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| day:<number>               | month, quarter, semester, year | Month: `day:1` to `day:31`<br>Year: `day:1` to `day:365`                                                                                                             |
| week:<number>              | month, quarter, semester, year | `week:1` to `week:52` (or `week:53`)                                                                                                                                 |
| <day_of_week>:first        | month, quarter, semester, year | `monday:first` to `sunday:first`                                                                                                                                     |
| <day_of_week>:last         | month, quarter, semester, year | `monday:last` to `sunday:last`                                                                                                                                       |
| <day_of_week>:<number>     | month, quarter, semester, year | `monday:1` to `monday:5` (month)<br>`monday:1` to `monday:14` (quarter)<br>`monday:1` to `monday:26` (semester)<br>`monday:1` to `monday:52` (or `monday:53`) (year) |

**Example: Date Reference**

```php
["date_from", "=", "date.this.year.first"]
```

This condition filters objects starting from the first day of the current year.

---

## Roles in Domains

In eQual, **roles** define permissions and access levels for users interacting with objects. Roles can be used in domains to enforce constraints such as Separation of Duty (SoD).

### Role Definitions

Roles are defined in the `getRoles` method and can include rights such as `create`, `read`, `update`, `delete`, and `manage`.

**Example: Role Definition**

```php
public static function getRoles() {
    return [
        "owner" => [
            "rights" => EQ_R_READ | EQ_R_UPDATE | EQ_R_DELETE | EQ_R_MANAGE
        ],
        "editor" => [
            "rights" => EQ_R_READ | EQ_R_UPDATE
        ],
        "viewer" => [
            "rights" => EQ_R_READ
        ]
    ];
}
```

**Example: Extended Role Definition**
```php
public static function getRoles() {
    return [
        "owner" => [
            "description" => "",
            "rights" => EQ_R_READ | EQ_R_UPDATE | EQ_R_DELETE | EQ_R_MANAGE
        ],
        "admin" => [
            "description" => "",
            "implied_by" => ['owner'],
            "rights" => EQ_R_READ | EQ_R_UPDATE | EQ_R_DELETE 
        ],
        "editor" => [
            "description" => "",
            "implied_by" => ['admin'],
            "rights" => EQ_R_READ | EQ_R_UPDATE
        ],
        "viewer" => [
            "description" => "",
            "implied_by" => ['editor'],
            "rights" => EQ_R_READ
        ]
    ];
}
```

### Role Assignment

Roles are assigned using `Assignment` objects, which maps users to roles for specific objects.

```sql
Assignment
    object_class
    object_id
    role
    user_id
```

---

## Selector Hierarchy

The **selector** defines the context of a value or sequence. It ensures clarity and avoids ambiguity when retrieving values.

### Selector Rules

- **Empty Selector**: Targets the most generic (global) value.
- **Non-Empty Selector**: Matches values with exactly those fields set, while all other fields are `null` (fields omitted in the selector are considered implicitly `null`).

#### Example: Selector

```php
$selector = [
    "user_id" => 1,
    "org_id" => null
];
```

This selector targets values specific to user `1` but not tied to any organization.

---

