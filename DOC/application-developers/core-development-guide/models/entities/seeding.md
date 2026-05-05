# Seeding

eQual includes an automatic data population mechanism called **seeding**. This feature generates objects automatically, which is especially useful during development and testing.

## Overview

Seeding allows developers and testers to work with consistent datasets by:

- Automatically generating objects based on [entity](entities.md) definitions
- Ensuring generated values are consistent with field descriptors from `getColumns()`
- Supporting relational data generation

---

## Seeding Schema

A seeding schema is a JSON array where each object represents a seeding operation for a specific data model. The file specifies:

- Which models to seed
- The quantity of records to create
- Field values to set
- Relationships between models

### Basic Example

```json
[
    {
        "name": "core\\alert\\MessageModel",
        "qty": 1,
        "fields": {
            "name": "Example model"
        },
        "relations": {
            "messages_ids": {
                "mode": "create",
                "qty": [5, 10],
                "fields": {
                    "severity": "notice"
                }
            }
        }
    }
]
```

---

## Schema Properties

### Root Object Properties

| **Property**    | **Type**             | **Description**                                                                            |
| --------------- | -------------------- | ------------------------------------------------------------------------------------------ |
| name            | `string`             | Fully qualified class name to seed.                                                        |
| lang            | `string`             | Defines the language for any localized fields. If not specified, defaults to DEFAULT_LANG. |
| qty             | `integer` \| `array` | Number of records to create. Use array `[min, max]` for random range.                      |
| fields          | `object`             | Field values to set on created objects.                                                    |
| set_object_data | `object`             | Maps fields from the created object to be used later in relationships domain.              |
| relations       | `object`             | Relational field generation settings.                                                      |

### Relation Descriptor Properties

| **Property** | **Type**             | **Description**                                                 |
| ------------ | -------------------- | --------------------------------------------------------------- |
| mode         | `string`             | How to handle related records.          |
| qty          | `integer` \| `array` | Number of related records to create/link.                       |
| fields       | `object`             | Field values for created related objects.                       |
| domain       | `array`              | Filters for selecting existing records. |

### Mode Descriptor Properties

| **Property**           | **Type** | **Description**                                                                                                   |
| ---------------------- | -------- | ----------------------------------------------------------------------------------------------------------------- |
| create                 | `string` | Create new related records with specified fields.                                                                 |
| use-existing           | `string` | Uses existing record(s), based on the given domain. If no existing record meets the criteria, fallback on create. |
| use-existing-or-create | `string` | Uses existing record(s) if found, otherwise creates a new one.                                                    |

!!! note "set_object_data"
    At any level, data can be arbitrary append to a global object reference, and can then be used in subsequent domains.

---

## Usage Examples

### Creating with Fixed Values

```json
[
    {
        "name": "demo\\User",
        "qty": 10,
        "fields": {
            "status": "active",
            "role": "member"
        }
    }
]
```

### Creating with Random Quantity

```json
[
    {
        "name": "demo\\Product",
        "qty": [50, 100],
        "fields": {
            "available": true
        }
    }
]
```

### Creating with Relations

```json
[
    {
        "name": "demo\\Order",
        "qty": 5,
        "relations": {
            "items_ids": {
                "mode": "create",
                "qty": [1, 5],
                "fields": {
                    "quantity": 1
                }
            }
        }
    }
]
```

### Linking to Existing Records

```json
[
    {
        "name": "demo\\Task",
        "qty": 20,
        "relations": {
            "user_id": {
                "mode": "link",
                "domain": [["status", "=", "active"]]
            }
        }
    }
]
```

---

