# Lists

Lists display multiple objects in a grid or table format, enabling users to browse, sort, filter, and perform bulk operations on collections.

**Purpose:** Display multiple objects in a grid or table format.

**Example:** A user list showing `name`, `email`, and `role`.

**Key Features:**

* Pagination, sorting, and filtering
* Domain-based filters applied dynamically (e.g., `domain=[name,like,John]`)
* Bulk actions on selected items
* Grouping and aggregation with operations
* Customizable columns and layout
* Inline actions for per-row interactions

---

## Header Configuration

The **header** section customizes the list view's header behavior and action buttons. For common header properties shared across all view types, refer to the [common view header](./common-structures/common-structures.md#header) documentation.

List-specific header properties include:

| **PROPERTY**      | **TYPE**                | **DESCRIPTION**                                                  |
| ----------------- | ----------------------- | ---------------------------------------------------------------- |
| `selection`       | [Selection](#selection) | Configuration for bulk actions available when items are selected |
| `actions`         | object                  | Fine-grained control over action button visibility and behavior  |
| `filters`         | object                  | Configuration for quick search and custom filter display         |
| `advanced_search` | `boolean` or object     | Show/hide and control the advanced search form                   |

### Selection

The `selection` property customizes bulk actions that apply to one or more selected items in the list.

**Structure:**

| **PROPERTY** | **TYPE**                              | **DESCRIPTION**                                                                                                                 |
| ------------ | ------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| `default`    | `boolean`                             | (optional)  Flag telling if the default actions have to be present in the available action to apply on current selection. (defaults to true) |
| `actions`    | array of [Action](#selection-actions) | (optional) Custom actions available for selected items                                                                          |

**Selection Field Properties:**

| **PROPERTY** | **TYPE** | **DESCRIPTION**                        |
| ------------ | -------- | -------------------------------------- |
| `type`       | `string` | Set to `"select"` for dropdown display |
| `values`     | `array`  | List of options for the dropdown       |


**Disable Selection Entirely:**

```json
{
  "header": {
    "selection": false
  }
}
```

**Custom Bulk Actions:**

```json
{
  "header": {
    "selection": {
      "default": false,
      "actions": [
        {
          "id": "ACTION.CLONE"
        },
        {
          "id": "header.selection.actions.mark_ignored",
          "label": "Mark as ignored",
          "icon": "done_all",
          "controller": "lodging_sale_booking_bankstatementline_bulk-ignore"
        }
      ]
    }
  }
}
```

#### Selection Actions

Actions in the `selection.actions` array can reference predefined action IDs (like `ACTION.CLONE`) or define custom actions with the following structure:

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                      |
| ------------ | --------- | -------------------------------------------------------------------- |
| `id`         | `string`  | Unique identifier for the action                                     |
| `label`      | `string`  | Button label displayed to the user                                   |
| `icon`       | `string`  | Icon identifier for the button                                       |
| `controller` | `string`  | [ORM/Entity controller](../../controllers-routing/controllers.md) to invoke when the action is triggered |
| `confirm`    | `boolean` | (optional) If `true`, display a confirmation dialog before execution |

### Actions

The `actions` property provides fine-grained control over predefined action buttons in list views.

| **ACTION ID** | **TYPE** | **DESCRIPTION**|
| ------------- | -------- |----------------------------------------------------------|
| `ACTION.SELECT` | `boolean` or array of action items | Controls the "Select" button in list view headers when `purpose` is set to `select`. Set to `true` to show the default select action, or provide an array of action items for a split-button with multiple options. |
| `ACTION.CREATE` | `boolean` or array of action items | Controls the "Create" button in list view headers. Set to `true` to show the default create action, or provide an array of action items for a split-button with multiple options. |
| `ACTION.CREATE_INLINE` | `boolean` or array of action items | Controls the availability of an inline "Create" action for each row in the list. Set to `true` to show the default inline create action, or provide an array of action items for a split-button with multiple options. |


**Control Options:**

* Use a `boolean` to enable or disable the action entirely
* Provide an object with `view`/`edit` keys to control behavior per mode
* Use an array of descriptors to define overloads or conditional behaviors with additional options

**Disable an Action Entirely:**

```json
{
  "header": {
    "actions": {
      "ACTION.CREATE": false,
      "ACTION.DELETE": false
    }
  }
}
```

**Control by Mode:**

```json
{
  "header": {
    "actions": {
      "ACTION.SAVE": {
        "view": false,
        "edit": true
      },
      "ACTION.EDIT": {
        "visible": ["support.default.user", "in", "user.groups"]
      }
    }
  }
}
```

**Override with Custom Configuration:**

```json
{
  "header": {
    "actions": {
      "ACTION.CREATE": [
        {
          "view": "form.create",
          "description": "Custom form for object creation.",
          "domain": ["parent_status", "=", "object.status"],
          "access": {
            "groups": ["admin"]
          },
          "controller": "custompackage_mode_update"
        }
      ]
    }
  }
}
```

**Override by Mode:**

```json
{
  "header": {
    "actions": {
      "ACTION.CREATE": {
        "view": [
          {
            "view": "form.create",
            "description": "Custom form for object creation."
          }
        ],
        "edit": false
      }
    }
  }
}
```

#### Inline Actions

Actions can be displayed directly on each row of the list instead of only in the header. Set `"inline": true` on an action descriptor to show its icon next to every line. This allows quick per-row interactions while maintaining a clean header.

Inline actions are particularly useful for operations like "Generate allocations," "Approve," or other per-item workflows.

**Example - Inline action in list view:**

```json
{
    "name": "Fund Request Lines",
    "description": "Fund request lines with amount and apportionment.",
    "operations": {
        "total": {
            "request_amount": {
                "operation": "SUM",
                "usage": "amount/money:2"
            },
            "allocated_amount": {
                "operation": "SUM",
                "usage": "amount/money:2"
            }
        }
    },
    "actions": [
        {
            "id": "action.generate_allocations",
            "inline": true,
            "label": "Generate allocations",
            "icon": "play_arrow",
            "description": "Generate the allocations of the fund request part.",
            "controller": "core_model_do",
            "params": {
                "entity": "realestate\\funding\\FundRequestLine",
                "action": "generate_allocation"
            },
            "confirm": true
        }
    ],
    "layout": {
        "items": [
            {
                "type": "field",
                "label": "condominium",
                "value": "condo_id",
                "width": "25%"
            },
            {
                "type": "field",
                "label": "Fund request",
                "value": "fund_request_id",
                "width": "25%"
            },
            {
                "type": "field",
                "label": "apportionment",
                "value": "apportionment_id",
                "width": "25%"
            },
            {
                "type": "field",
                "label": "Requested amount",
                "value": "request_amount",
                "width": "15%"
            },
            {
                "type": "field",
                "label": "Allocated amount",
                "value": "allocated_amount",
                "width": "15%"
            }
        ]
    }
}

```

##### Routes

Ony inline routes are supported and handled as actions opening the context given the route. 

### Advanced Search

The `advanced_search` property controls whether an advanced search form is displayed. This is useful when the list uses a custom [controller](../../controllers-routing/controllers.md) with parameters that function as searchable fields.

If a custom controller is specified (other than the default `core_model_collect`) and a view is associated with it, the controller's parameters are treated as searchable fields.

**Hide Advanced Search:**

```json
{
  "advanced_search": false
}
```

**Show and Auto-open Advanced Search:**

```json
{
  "advanced_search": {
    "show": true,
    "open": true
  }
}
```

---

## Layout Configuration

List layouts define how objects are displayed in table columns. Each item descriptor corresponds to a column in the table.

**Structure:**

| **PROPERTY** | **TYPE**                | **DESCRIPTION**            |
| ------------ | ----------------------- | -------------------------- |
| `items`      | array of [Item](#items) | List of columns to display |

### Items

**Items** define how a field or label from an object is displayed as a table column.

**Structure:**

| **PROPERTY** | **TYPE**             | **DESCRIPTION**                                                                    |
| ------------ | -------------------- | ---------------------------------------------------------------------------------- |
| `sortable`     | `boolean`            | If `true`, the column is sortable by clicking the header                           |

### Widget

Widgets configure how items are displayed and behave. Refer to the [widgets](./widgets/widgets.md) documentation for complete details.

**Properties Common to All Relational Widgets**

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                     |
| ------------ | --------- | ------------------------------------------------------------------- |
| `sortable`   | `boolean` | If `true`, users can sort the list by clicking this column header   |
| `domain`     | `array`   | [Domain](../../models/domains.md) conditions affecting display                         |

**Relational Field Properties (many2one, many2many):**

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                              |
| ------------ | --------- | ---------------------------------------------------------------------------- |
| `header`     | object    | Override the [header](./common-structures/common-structures.md#header) configuration for the relational view            |
| `controller` | `string`  | Custom controller for fetching related data                                  |
| `group_by`   | `array`   | Group related items by field                                                 |
| `sort`       | `string`  | Sort direction for related items                                             |
| `order`      | `string`  | Field to sort related items by                                               |
| `start`      | `integer` | Pagination offset for related items                                          |
| `limit`      | `integer` | Maximum number of related items to display                                   |

#### Relational Field Examples

**one2many Default Configuration:**

```json
{
  "widget": {
    "header": {
      "selection": {
        "default": true,
        "actions": [
          {"id": "ACTION.CREATE"},
          {"id": "ACTION.REMOVE"}
        ]
      }
    }
  }
}
```

**Behavior:**

* View Mode: "Create" button available
* 
* Edit Mode: "Create" button and selection with "Remove" action

**Custom Bulk Actions:**

```json
{
  "widget": {
    "header": {
      "selection": {
        "default": false,
        "actions": [
          {"id": "ACTION.EDIT_INLINE"},
          {"id": "ACTION.CLONE"}
        ]
      }
    }
  }
}
```

**Behavior:**

* View Mode: "Create" button available
  
* Edit Mode: "Create" button and selection with "Edit inline" and "Duplicate" actions

**No Selection or Actions:**

```json
{
  "widget": {
    "header": {
      "selection": false,
      "actions": {
        "ACTION.CREATE": false
      }
    }
  }
}
```

**Behavior:**

* View Mode: No action buttons

* Edit Mode: No action buttons, selection disabled

## Sorting and Pagination

### Order

The `order` property specifies which fields to sort results by. Multiple fields can be specified, separated by commas.

```json
{
  "order": "sku,product_model_id"
}
```

### Sort

The `sort` property indicates the sort direction(s) applied to fields specified in `order`. Accepted values are:

* `"asc"` - ascending order
* `"desc"` - descending order

For multiple fields, provide a comma-separated list matching the order of fields. Unspecified fields default to `"asc"`.

**Example - Mixed sort directions:**

```json
{
  "order": "sku,product_model_id",
  "sort": "asc,desc"
}
```

This sorts by `sku` ascending, then by `product_model_id` descending.

### Limit

The `limit` property specifies the maximum number of items displayed per page.

```json
{
  "limit": 50
}
```

Refer to the [contexts](../contexts.md) documentation for additional pagination details.

### Width

The `width` property defines the list's width relative to the screen or its container. Minimum width is set to 10%. The columns widths will adapt proportionally to fill the available space (even if the total of column widths is less than 100%).

```json
{
  "width": "80%"
}
```

### Visible

The `visible` property controls whether the column is displayed based on a boolean value or a [domain](../../models/domains.md) condition.

```json
{
  "visible": ["user.groups", "in", ["admin", "manager"]]
}
```

---

## Data Retrieval

### Controller

The `controller` property specifies which [ORM/Entity controller](../../controllers-routing/controllers.md) fetches the object collection for the view. The default is `core_model_collect` (aliased as `model_collect`).

```json
{
  "controller": "core_model_collect"
}
```

---

## Filtering

### Filters

Pre-defined filters provide users with quick filtering options complementing their custom filters. Each filter adds a clause to the domain when applied.

**Structure:**

| **PROPERTY**  | **TYPE**            | **DESCRIPTION**                                                           |
| ------------- | ------------------- | ------------------------------------------------------------------------- |
| `id`          | `string`            | Unique identifier for translation purposes                                |
| `label`       | `string`            | Display name for the filter button                                        |
| `description` | `string`            | (optional) Tooltip explaining the filter's purpose                        |
| `clause`      | `array` or `domain` | Filter conditions to apply (both clause and domain formats are supported) |

**Example:**

```json
{
  "filters": [
    {
      "id": "lang.french",
      "label": "French",
      "description": "Users with locale set to French.",
      "clause": ["language", "=", "fr"]
    },
    {
      "id": "status.active",
      "label": "Active",
      "clause": ["status", "=", "active"]
    }
  ]
}
```

---

## Grouping and Aggregation

### Group By

The `group_by` property organizes list results into collapsed/expandable groups. Each item in the array is either a simple field name or a descriptor defining grouping behavior and operations.

**Simple Grouping:**

```json
{
  "group_by": ["date"]
}
```

**Grouping with Operations:**

```json
{
  "group_by": [
    {
      "field": "product_id",
      "operation": ["SUM", "object.qty"],
      "order": "name",
      "open": false
    }
  ]
}
```

**Mixed Simple and Complex Groups:**

```json
{
  "group_by": [
    "date",
    {
      "field": "product_id",
      "operation": ["SUM", "object.qty"],
      "open": false
    }
  ]
}
```

**Group By Structure:**

| **PROPERTY** | **TYPE**            | **DESCRIPTION**                                                                                            |
| ------------ | ------------------- | ---------------------------------------------------------------------------------------------------------- |
| `field`      | `string`            | The field to group by. Can be a scalar field (e.g., `date`) or a relation (e.g., `product_id`)             |
| `operation`  | `array` or `string` | (optional) Operation to perform on grouped data. Uses array syntax like `["SUM", "object.qty"]`            |
| `order`      | `string`            | Field used to sort groups. Defaults to `"name"` for related objects, or the field value itself for scalars |
| `open`       | `boolean`           | (optional) If `true`, groups expand by default; if `false`, groups are collapsed by default                |
| `operations` | object              | (optional) Additional named calculations displayed for each group (see [Operations](#operations))          |

**Sorting Behavior:**

* For scalar fields: Results are sorted by the field value
* For relation fields: Results are sorted by the related object's `name` field (customizable via `order`)

### Operations

Operations apply calculations (aggregations) to list data, displaying summary rows with computed values. Operations can appear at two levels: as part of `group_by` definitions or in the global `operations` property.

**Global Operations Structure:**

```json
{
  "operations": {
    "total": {
      "total_paid": {
        "id": "operations.total.total_paid",
        "label": "Total received",
        "operation": "SUM",
        "usage": "amount/money:2"
      },
      "total_due": {
        "id": "operations.total.total_due",
        "label": "Total due",
        "operation": "SUM",
        "usage": "amount/money:2"
      }
    }
  }
}
```

**Operation Properties:**

| **PROPERTY** | **TYPE**            | **DESCRIPTION**                                                                          |
| ------------ | ------------------- | ---------------------------------------------------------------------------------------- |
| `operation`  | `string` or `array` | The operation to apply (e.g., `"SUM"`, `["SUM", "object.field"]`)                        |
| `usage`      | `string`            | [Usage](../../models/entities/fields.md#usages) hint for formatting the result (e.g., `amount/money:2`, `numeric/integer`) |
| `label`      | `string`            | (optional) Display label for the operation result                                        |
| `id`         | `string`            | (optional) Identifier for translation purposes                                           |

#### Unary Operators

| **OPERATOR** | **SYNTAX**                | **DESCRIPTION**                     |
| ------------ | ------------------------- | ----------------------------------- |
| SUM          | `['SUM', object.field]`   | Sum of all values in the field      |
| AVG          | `['AVG', object.field]`   | Average (equivalent to SUM / COUNT) |
| COUNT        | `['COUNT', object.field]` | Number of non-null values           |
| MIN          | `['MIN', object.field]`   | Minimum value                       |
| MAX          | `['MAX', object.field]`   | Maximum value                       |

#### Binary Operators

| **OPERATOR** | **RESULT**                     | **SYNTAX**    |
| ------------ | ------------------------------ | ------------- |
| `+`          | Sum of `a` and `b`             | `['+', a, b]` |
| `-`          | Difference between `a` and `b` | `['-', a, b]` |
| `*`          | Product of `a` by `b`          | `['*', a, b]` |
| `/`          | Division of `a` by `b`         | `['/', a, b]` |
| `%`          | Modulo `b` of `a`              | `['%', a, b]` |
| `^`          | `a` to the power of `b`        | `['^', a, b]` |

---

## Complete List View Example

```json
{
  "name": "Bookings",
  "description": "Manage all bookings and their status.",
  "domain": ["archived", "=", false],
  "controller": "core_model_collect",
  "limit": 50,
  "order": "created,booking_reference",
  "sort": "desc,asc",
  "filters": [
    {
      "id": "status.pending",
      "label": "Pending",
      "clause": ["status", "=", "pending"]
    },
    {
      "id": "status.confirmed",
      "label": "Confirmed",
      "clause": ["status", "=", "confirmed"]
    }
  ],
  "group_by": [
    {
      "field": "booking_date",
      "open": true
    }
  ],
  "operations": {
    "summary": {
      "total_amount": {
        "label": "Total",
        "operation": "SUM",
        "usage": "amount/money:2"
      }
    }
  },
  "layout": {
    "items": [
      {
        "type": "field",
        "label": "Reference",
        "value": "booking_reference",
        "width": 20
      },
      {
        "type": "field",
        "label": "Customer",
        "value": "customer_id",
        "width": 25
      },
      {
        "type": "field",
        "label": "Status",
        "value": "status",
        "width": 15,
        "widget": {
          "type": "select"
        }
      },
      {
        "type": "field",
        "label": "Amount",
        "value": "total_amount",
        "width": 20,
        "widget": {
          "usage": "amount/money:2"
        }
      }
    ]
  },
  "header": {
    "layout": "full",
    "filters": true,
    "selection": {
      "default": true,
      "actions": [
        {"id": "ACTION.EDIT_BULK"},
        {"id": "ACTION.ARCHIVE"}
      ]
    },
    "actions": {
      "ACTION.CREATE": true,
      "ACTION.DELETE": false
    }
  }
}
```

---