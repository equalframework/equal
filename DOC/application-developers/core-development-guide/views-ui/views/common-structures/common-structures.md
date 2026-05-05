# Views Common Structure

Views in eQual share a common set of properties that define their behavior, appearance, and access control. Additional properties are available for specific view types (List, Menu, Dashboard, Form).

---

## View-Type-Specific Properties

For View-Type Specific properties, see the following documentation:

* [List Views](lists.md)
* [Form Views](forms.md)
* [Menu Views](menus.md)
* [Dashboard Views](dashboards.md)
* [Search Views](search.md)

---

## Common Properties

All view types support the following core properties:

| **PROPERTY**  | **TYPE**                          | **DESCRIPTION**                                                                                |
| ------------- | --------------------------------- | ---------------------------------------------------------------------------------------------- |
| `name`        | `string`                          | Name of the view displayed in the application                                                  |
| `description` | `string`                          | Description of the view and its purpose                                                        |
| `controller`  | `string`                          | Data controller used to fetch objects from the database (defaults to `core_model_collect`)     |
| `mode`        | `string`                          | Display mode for the view                                                                      |
| `layout`      | `Layout`                          | Layout containing items for rendering the view (see [Layout](#layout) documentation)           |
| `order`       | `string`                          | Default field(s) to order by for list views (defaults to `id`): for lists                      |
| `sort`        | `string`                          | Default sort direction for list views: `asc` or `desc` (defaults to `asc`): for lists          |
| `start`       | `integer`                         | Pagination start index for list and search views (default to `0`)                              |
| `limit`       | `integer`                         | Number of items fetched per page in list and search views (default to `25`)                    |
| `header`      | array of [Header](#header)        | Configuration to override the default header behavior                                          |
| `filters`     | array of [Filter](#filters)       | Associative array of filters with name and clauses                                             |
| `actions`     | array of [Action](#actions)       | Custom actions applicable to the view context (typically for form views)                       |
| `routes`      | array of [Route](#routes)         | Contextual links to other parts of the application                                             |
| `access`      | array of [Access](#access)        | Access control rules to restrict view visibility by user group or login                        |
| `group_by`    | array of [GroupBy](#group-by)     | Fields to group by in list views (default is empty array) for lists                            |
| `exports`     | array of [Export](#exports)       | Export/print configurations                                                                    |
| `domain`      | array of [Domain](#domain)        | Domain to filter which items from the input collection will be displayed                       |
| `layout`      | array of [Layout](#layout)        | Layout configuration for the view, defining how items are arranged on the screen               |
| `operations`  | array of [Operation](#operations) | (for list views) Definitions of operations to apply on columns or groups, like SUM, COUNT, AVG |

### Header

The `header` section allows customization of the view's header behavior and layout.

**Structure:**

| **PROPERTY**      | **TYPE**                             | **DESCRIPTION**                                                                                                                                    |
| ----------------- | ------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------- |
| `actions`         | [Header Action](#header-actions)     | Configuration of action buttons shown in the header (see [Header Actions](#header-actions))                                                        |
| `selection`       | [Selection](#selection) or `boolean` | Descriptor for the actions that can be applied when one or more items are selected in the list                                                     |
| `filters`         | `boolean`                            | Flag to display or hide the default filtering input                                                                                                |
| `layout`          | `string`                             | `full` (default) or `inline`. The `inline` mode displays a compact list with minimal navigation and icon-based actions, ideal for embedded layouts |
| `advanced_search` | `boolean`                            | Flag to display or hide the advanced search button in the header, which opens a sidebar for building complex queries (only for list views)         |
| `mode`            | `string`                             | For chart views only (`chart` or `grid`)                                                                                                           |

**Example - Inline layout:**

```json
{
  "header": {
    "layout": "inline",
    "actions": {
      "ACTION.CREATE_INLINE": true
    }
  }
}
```

---

#### Header Actions

Make sure not to mix up the "actions" section with the header "actions" subsection. The former lists the actions that are available for the whole view (generally form views) while the latter can be used to allow or prevent specific actions on selected objects (generally list views).

**Note:** Predefined action IDs must be used; custom IDs are not supported for header actions.

Default actions can be hidden by setting `visible` to `false`. To define a split-button (multiple action variants), use an array of action items.

For a complete list of predefined action IDs and their default behaviors, see the [Header section: Views & Actions](#header) documentation.

**Example:**

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
      ],
      "ACTION.SELECT": false,
      "ACTION.SAVE": [
        {"id": "SAVE_AND_CONTINUE"},
        {"id": "SAVE_AND_CLOSE"}
      ],
      "ACTION.CANCEL": [
        {"id": "CANCEL_AND_VIEW"}
      ]
    }
  }
}
```

#### Selection

The `selection` property allows to customize the list of bulk actions that are available when one or more items are selected.

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                                                                                             |
| ------------ | --------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| `default`    | `boolean` | Flag telling if the default actions have to be present in the available action to apply on current selection                                |
| `actions`    | `array`   | An array of action items that can be applied on current selection                                                                           |
| `id`         | `string`  | Identifier for translation and reference purposes                                                                                           |
| `primary`    | `boolean` | If `true`, this action is the default selection action triggered when clicking on an item without choosing a specific action from the menu. |
| `visible`    | `array`   | (optional) [Domain](../../../models/domains.md) conditions to determine if the selection action is shown for a given item                   |

**Example: Allow only `ACTION.CLONE` and a custom action**

```json
"header": {
    "filters": {
        "custom": true,
        "quicksearch": false
    },
    "selection": {
        "default" : false,
        "actions" : [
            {
                "id": "ACTION.CLONE"
            },
            {
                "id": "header.selection.actions.mark_ignored",
                "label": "Mark as ignored",
                "icon": "",
                "controller": "lodging_sale_booking_bankstatementline_bulk-ignore"
            }
        ]
    }
}

```

---

### Filters

The `filters` property customizes the filtering features available in the view header. Each filter allows users to apply predefined conditions to the displayed data.

**Structure:**

| **PROPERTY**  | **TYPE**  | **DESCRIPTION**                                                                                                                  |
| ------------- | --------- | -------------------------------------------------------------------------------------------------------------------------------- |
| `id`          | `string`  | Unique identifier for translation purposes                                                                                       |
| `label`       | `string`  | Default display name if no translation is set                                                                                    |
| `description` | `string`  | Description explaining the filter's purpose                                                                                      |
| `clause`      | `array`   | [Clause](../../../models/domains.md#clauses) that will be added to the domain when the filter is applied                                                       |
| `quicksearch` | `boolean` | If `true`, the filter is applied as the user types in the quicksearch input, allowing for dynamic filtering based on the clause. |

**Example:**

```json
{
  "filters": [
    {
      "id": "lang.french",
      "label": "French",
      "description": "French speaking people",
      "clause": ["language", "=", "fr"]
    }
  ]
}
```

---

### Actions

The `actions` property defines custom actions for the view. Each action corresponds to a button that, when clicked, invokes a specified controller and automatically refreshes the view upon completion.

**Context:** Root-level actions are typically used in form views. For list views, use [header.actions](#header-actions) to control actions on selected objects.

**Structure:**

| **PROPERTY**  | **TYPE**          | **DESCRIPTION**                                                                                                                                           |
| ------------- | ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `id`          | `string`          | Identifier for translation and reference purposes                                                                                                         |
| `label`       | `string`          | Button label displayed to the user                                                                                                                        |
| `description` | `string`          | Description shown when the user hovers over the button                                                                                                    |
| `controller`  | `string`          | [ORM/Entity controller](../../../controllers-routing/controllers.md) invoked when the action is triggered. The current object's `id` is sent as a parameter by default                           |
| `visible`     | `array`           | (optional) [Domain](../../../models/domains.md) conditions to determine if the action button is shown                                                                           |
| `confirm`     | `boolean`         | (optional) If `true`, a confirmation dialog appears before executing the action                                                                           |
| `params`      | object            | (optional) Associative array mapping fields to values. Values can reference user properties (e.g., `user.login`) or object properties (e.g., `object.id`) |
| `access`      | [Access](#access) | (optional) Access control to limit action visibility and invocation                                                                                       |

**Example:**

```json
{
  "actions": [
    {
      "id": "action.option",
      "label": "Set as Option",
      "description": "Block rental units without claiming funding.",
      "controller": "lodging_booking_option",
      "visible": ["status", "=", "quote"],
      "confirm": true,
      "params": {
        "id": "object.id"
      }
    },
    {
      "id": "action.validate",
      "label": "Confirm Booking",
      "description": "Block units and set up invoicing plan.",
      "controller": "lodging_booking_confirm",
      "visible": ["status", "in", ["quote", "option"]]
    }
  ]
}
```

If the target controller requires parameters, a dialog prompts the user for values. If no parameters are required but `confirm` is `true`, a confirmation dialog appears instead. See the [action flow diagram](/_assets/img/eq_confirm_diagram.png) for details.

---

### Routes

Routes define contextual links to other parts of the application, displayed within the view to guide user navigation.

**Structure:**

| **PROPERTY** | **TYPE** | **DESCRIPTION**                               |
| ------------ | -------- | --------------------------------------------- |
| `package`    | `string` | Target package of the route (e.g., `lodging`) |

---

### Access

Access control restricts view visibility to specific users and groups.

**Structure:**

| **PROPERTY** | **DESCRIPTION**                                        |
| ------------ | ------------------------------------------------------ |
| `groups`     | Array of group names whose members can access the view |
| `users`      | Array of user logins that can access the view          |

**Example:**

```json
{
  "access": {
    "groups": ["users"]
  }
}
```

---

### GroupBy

The `group_by` property defines fields to group by in list views, allowing for aggregated displays of data. Groupings can be defined as simple field names or as objects with additional configuration for operations and sorting.

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                                                                                   |
| ------------ | --------- | --------------------------------------------------------------------------------------------------------------------------------- |
| `field`      | `string`  | Name of the field to group by                                                                                                     |
| `operation`  | `array`   | Operation to apply on the grouped field (e.g., `SUM`, `COUNT`, `AVG`) and the target field for the operation (e.g., `object.qty`) |
| `order`      | `string`  | Sort order for the grouped results: `asc` or `desc` (defaults to `asc`)                                                           |
| `open`       | `boolean` | If `true`, the group is expanded by default in the view                                                                           |
| `operations` | `array`   | Array of operations to apply to specific fields, allowing for multiple calculations (e.g., total count, average)                  |

**Example 1: Simple group by field**
```json
{
  "group_by": ["date"]
}
```

**Example 2: Group by field with operation and sort order**
```json
{
  "group_by": [
    {
      "field": "time_slot_id",
      "operation": ["SUM", "object.qty"],
      "order": "asc"
    }
  ]
}
```

---

### Exports

The `exports` property defines document export/print configurations for list views. Each export allows users to generate and print documents (e.g., contracts, reports) based on view data.

**Structure:**

| **PROPERTY**  | **TYPE** | **DESCRIPTION**                                                                                |
| ------------- | -------- | ---------------------------------------------------------------------------------------------- |
| `id`          | `string` | Unique identifier for the export                                                               |
| `label`       | `string` | Button label displayed to the user                                                             |
| `icon`        | `string` | Icon identifier (e.g., `print`)                                                                |
| `description` | `string` | Description of what the export generates                                                       |
| `controller`  | `string` | [ORM/Entity controller](../../../controllers-routing/controllers.md) that handles the export (e.g., `lodging_booking_print-contract`) |
| `view`        | `string` | View ID for export display (typically `print.default`)                                         |
| `visible`     | `array`  | (optional) [Domain](../../../models/domains.md) conditions to determine if the export button is displayed            |

**Example:**

```json
{
  "exports": [
    {
      "id": "export.print.contract",
      "label": "Print contract",
      "icon": "print",
      "description": "Print the contract related to this booking.",
      "controller": "lodging_booking_print-contract",
      "view": "print.default",
      "visible": ["status", "=", "quote"]
    }
  ]
}
```

---

### Domain

The `domain` property conditionally filters which data is displayed in the view. It uses the [domain](../../../models/domains.md) syntax to define filter criteria.

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                                |
| ------------ | -------- | ------------------------------------------------------------------------------ |
| `clause`     | `array`  | [Clause](../../../models/domains.md#clauses) that will be added to the domain when the filter is applied     |
| `domain`     | `array`  | (optional) Additional [domain](../../../models/domains.md) conditions to determine filter visibility |


**Example:**

```json
{
  "domain": ["type", "<>", "I"]
}
```

---

### Layout

Layout holds the structure of the view and defines how items are arranged on the screen. The main layout types are:

* `items` : defining a flat list of items to display
* `groups`, `sections`, `rows`, `columns` : defining nested structures (dependent on the view type) to organize items in more complex arrangements


#### Items

| **PROPERTY**  | **TYPE**          | **DESCRIPTION**                                                                |
| ------------- | ----------------- | ------------------------------------------------------------------------------ |
| `description` | `string`          | Description of the item, shown in a tooltip or details view                    |
| `type`        | `string`          | (required) Type of the item (`field` (interactive) or `label` (static text))   |
| `id`          | `string`          | Unique identifier, used for translations (overrides label and value)           |
| `label`       | `string`          | Custom title to override the field's default label                             |
| `value`       | `string`          | Static text content for labels, or field name for fields                       |
| `width`       | `percentage`      | Width of the item as a percentage of its parent (0-100%)                       |
| `readonly`    | `boolean`         | If `true`, the field cannot be edited in creation/edit contexts                |
| `visible`     | `boolean`         | If `false`, the item is not displayed                                          |
| `help`        | `string`          | Help text displayed for the field, guiding the user on what to enter           |
| `align`       | `string`          | Alignment of the item: `left`, `center`, `right`                               |
| `usage`       | `string`          | Usage hint for displaying the item (e.g., `amount/money:2`, `numeric/integer`) |
| `widget`      | [Widget](#widget) | Configuration properties for the item's display and behavior                   |

| `foreign_object` | `string`             | (for fields) Reference to a related object property                              |
| `foreign_field`  | `string`             | (for fields) Reference to a related field to display                             |
| `selection`      | [Selection](#selection) | (for fields) Configuration for selection behavior and available options |
| ``               |

---

### Widget

Widgets can be used to set properties that depend on the view type. For more details on available widget types and their specific properties, see the [Widgets](../widgets.md) documentation.

| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                                 |
| ------------ | --------- | ------------------------------------------------------------------------------- |
| `link`       | `boolean` | If `true`, the item value displays as a clickable link                          |
| `sortable`   | `boolean` | If `true`, users can sort the list by clicking this column header               |
| `type`       | `string`  | Overrides the default display type (e.g., `text`, `select`, `date`, `one2many`) |
| `usage`      | `string`  | Overrides the field's [usage](../../../models/entities/fields.md#usages) to customize formatting                     |
| `domain`     | `array`   | [Domain](../../../models/domains.md) conditions affecting display                                     |

---

### Operations

Operations apply calculations (like SUM, COUNT, AVG) to columns in list views, displaying aggregate results. Operations can be defined at three levels:

#### In `group_by` (Simple Field)

Group results by a field without additional operations:

```json
{
  "group_by": ["date"]
}
```

#### In `group_by` (As Object)

Apply an operation while grouping, with options like sort order:

```json
{
  "group_by": [
    {
      "field": "time_slot_id",
      "operation": ["SUM", "object.qty"],
      "order": "asc"
    }
  ]
}
```

#### In `operations` (Global)

Define named calculation rows independent from groupings. Each row can contain multiple named operations:

**Structure:**

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                                          |
| ------------ | -------- | ---------------------------------------------------------------------------------------- |
| `operation`  | `string` | The operation to apply (e.g., `SUM`, `COUNT`, `AVG`)                                     |
| `usage`      | `string` | [Usage](../../../models/entities/fields.md#usages) hint for displaying the result (e.g., `amount/money:2`, `numeric/integer`) |
| `prefix`     | `string` | (optional) String prepended to the result                                                |
| `suffix`     | `string` | (optional) String appended to the result                                                 |

**Example:**

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
        "operation": "SUM",
        "usage": "amount/money:2"
      }
    }
  }
}
```

---