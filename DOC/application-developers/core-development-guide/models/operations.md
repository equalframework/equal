# Operations

**Operations** in eQual define aggregation or computation logic to be applied to one or more fields of a dataset—typically in a [list view](../views-ui/views/lists.md). They are essential for displaying totals, averages, counts, or custom calculations directly in the UI.

---

## Overview

Operations allow you to perform calculations on your data, such as summing amounts, counting records, or computing averages. These are especially useful for dashboards, reports, and summary views.

Operations are defined using a functional-style array syntax, and can reference fields using the `object.` prefix.

---

## Types of Operators

There are two main types of operators:

### Unary Operators

Unary operators take a single operand, which can be a value or a field reference (often representing an array of values).

Supported unary operators:

* **ABS**: Absolute value
* **AVG**: Average
* **COUNT**: Count of values
* **DIFF**: Difference (single operand, context-specific)
* **MAX**: Maximum value
* **MIN**: Minimum value
* **SUM**: Sum of values

### Binary Operators

Binary operators take two operands. Each operand can be another operation, a value, or a field reference.

Supported binary operators:

* `'+': Addition
* `'-': Subtraction
* `'*': Multiplication
* `'/': Division
* `'%': Modulo
* `'^': Exponentiation

---

## Syntax

Operations use a functional array syntax. Field references use the `object.` prefix followed by the field name.

* **Unary operator example:**

    ```json
    ["SUM", "object.qty"]
    ```

* **Binary operator example (nested):**

    ```json
    ["/", ["SUM", "object.total"], ["COUNT", "object.id"]]
    ```

    This divides the sum of `total` by the count of `id`, effectively calculating an average.

---

## Operation Object Format

Each operation object can define the following properties:

| Property    | Description                                                         |
| ----------- | ------------------------------------------------------------------- |
| `operation` | The operator (e.g., `SUM`, `COUNT`) or a nested operation array     |
| `usage`     | Output format (e.g., `amount/money:2` for currency with 2 decimals) |
| `label`     | Display label shown in the UI (optional)                            |
| `id`        | Translation key for [translation](../i18n/i18n-overview.md) support (optional)          |
| `suffix`    | Text appended after the result (optional)                           |

For more on output formatting, see [Field Usages](./entities/fields/#usages).

---

## Examples

### Summing Monetary Values

```json
"operations": {
    "total": {
        "total_paid": {
            "id": "operations.total.total_paid",
            "label": "Total received",
            "operation": "SUM",
            "usage": "amount/money:2"
        },
        "total_due": {
            "operation": "SUM",
            "usage": "amount/money:2"
        }
    }
}
```

### Counting Records with a Suffix

```json
"operations": {
    "total": {
        "rental_unit_id": {
           "operation": "COUNT",
           "usage": "numeric/integer",
           "suffix": "p."
        }
    }
}
```

!!! note "More examples"
    For more examples of operations, see the [eQual Cheat Sheet](../../how-tos-references/cheat-sheet.md).

---
