# Forms

Forms enable creation and modification of individual entities. They provide input widgets for each field, with dynamic validation and behavior based on context.

**Purpose:** Enable creation or modification of a single entity.

**Example:** A form to update user details.

**Key Features:**

* Input widgets for each field
* Dynamic validation and behavior based on context
* Layout customization through groups, sections, and rows
* Conditional display of fields and sections
* Relational field management for one2many and many2many relationships
* Navigation routes for related views and external resources

---

## Layout Structure

Form views use a **layout** element containing all information needed to display a single object. Layouts are organized hierarchically to provide flexible page organization.

**Layout Hierarchy:**

Layout → Groups → Sections → Rows → Columns → Items → Widgets

### Groups

**Groups** are horizontal containers that organize a form into logical sections. When rendered, groups are displayed one below another.

| **PROPERTY** | **TYPE**                      | **DESCRIPTION**                                                         |
| ------------ | ----------------------------- | ----------------------------------------------------------------------- |
| `id`         | `string`                      | Unique identifier, used for translations                                |
| `label`      | `string`                      | (optional) Display name if no translation is available                  |
| `visible`    | `array` or boolean            | (optional) [Domain](../../models/domains.md) conditions to conditionally display the group |
| `sections`   | array of [Section](#sections) | List of sections (displayed as tabs if more than one)                   |

### Sections

A **section** contains a portion of the layout presented in rows and columns. If a group contains multiple sections, they are presented as tabs.

| **PROPERTY** | **TYPE**              | **DESCRIPTION**                                                           |
| ------------ | --------------------- | ------------------------------------------------------------------------- |
| `id`         | `string`              | Unique identifier, used for translations                                  |
| `label`      | `string`              | (optional) Display name if no translation is available                    |
| `visible`    | `array` or boolean    | (optional) [Domain](../../models/domains.md) conditions to conditionally display the section |
| `rows`       | array of [Row](#rows) | List of rows organizing the section's content                             |

### Rows

**Rows** organize content horizontally within a section, containing columns that control layout and spacing.

| **PROPERTY** | **TYPE**                    | **DESCRIPTION**                                                       |
| ------------ | --------------------------- | --------------------------------------------------------------------- |
| `id`         | `string`                    | Unique identifier, used for translations                              |
| `label`      | `string`                    | (optional) Display name if no translation is available                |
| `visible`    | `array` or boolean          | (optional) [Domain](../../models/domains.md) conditions to conditionally display the row |
| `columns`    | array of [Column](#columns) | List of columns within the row                                        |

| `width`      | `percentage`                | (optional) Width of the row as a percentage of its parent (0-100%)        |
| `align`      | `string`                    | (optional) Alignment of columns within the row: `left`, `center`, `right` |

### Columns

**Columns** define vertical divisions within rows, containing items that represent actual fields or labels.

| **PROPERTY** | **TYPE**                | **DESCRIPTION**                                                            |
| ------------ | ----------------------- | -------------------------------------------------------------------------- |
| `id`         | `string`                | Unique identifier, used for translations                                   |
| `label`      | `string`                | (optional) Display name if no translation is available                     |
| `visible`    | `array` or boolean      | (optional) [Domain](../../models/domains.md) conditions to conditionally display the column   |
| `width`      | `percentage`            | Width of the column as a percentage of its parent (0-100%)                 |
| `align`      | `string`                | (optional) Alignment of items within the column: `left`, `center`, `right` |
| `items`      | array of [Item](#items) | List of items (fields or labels) in the column                             |

### Items

**Items** describe how a field or label from a Model is displayed in the form view. Each item can be a field (interactive) or a label (static text). See [common structures](./common-structures/common-structures.md) for detailed item properties.

### Widget

Widgets configure how items are displayed and behave. Full documentation is available in the [widgets](./widgets/widgets.md) guide.

**Relational fields (one2many, many2many):**
| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                                                                                                                               |
| ------------ | -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `view`       | `string` | Specifies the view ID to use for displaying the relation                                                                                                                      |
| `header`     | `object` | Overrides the [header](./common-structures/common-structures.md#header) configuration for the relational view. Possible values are `domain` and `visible`, which accept the same parameters as their header counterparts |
| `domain`     | `array`  | Overrides the [domain](../../models/domains.md) to filter displayed relations                                                                                                                    |


**one2many**
| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                                                     |
| ------------ | -------- | --------------------------------------------------------------------------------------------------- |
| `autoselect` | `boolean` | If `true`, automatically opens the form view when only one related record exists                     |

**many2one**
| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                                                     |
| ------------ | -------- | --------------------------------------------------------------------------------------------------- |
| `fields`     | `array`  | List of sub-field names to load (e.g., `customer_id`, `customer_identity_id.name`). Necessary when sub-fields are referenced in domains or visibility conditions. Default fields are `id` and `name` |

**Example - many2one with sub-fields:**

```json
{
  "type": "field",
  "label": "Booking",
  "value": "booking_id",
  "width": 100,
  "widget": {
    "fields": ["customer_id", "customer_identity_id.name"],
    "readonly": true
  }
}
```

#### Controlling Relational Field Actions

For relational fields, the `header` configuration controls which action buttons are displayed. For more information on available actions, see the [Views & Actions](./common-structures/views-actions.md) documentation.

---

## Routes

Forms can include a **routes** element that displays navigation buttons on the right panel, linking to related views or external resources.

**Structure:**

| **PROPERTY**  | **TYPE**                   | **DESCRIPTION**                                                                              |
| ------------- | -------------------------- | -------------------------------------------------------------------------------------------- |
| `id`          | `string`                   | Unique identifier for the route                                                              |
| `label`       | `string`                   | Button label displayed to the user                                                           |
| `description` | `string`                   | (optional) Tooltip text explaining the route's purpose                                       |
| `icon`        | `string`                   | Icon identifier (e.g., `library_books`, `print`, `room_service`)                             |
| `route`       | `string`                   | Navigation path or external URL. Supports dynamic values like `object.id`                    |
| `context`     | `array` of [Context](../contexts.md) | (optional) Context object specifying entity, view, domain, and reset behavior                |
| `target`      | `string`                   | (optional) Target for opening the route: `_blank` for new window, default is current context |
| `absolute`    | `boolean`                  | (optional) If `true`, treats the route as an absolute URL                                    |
| `visible`     | `array` or `boolean`       | (optional) [Domain](../../models/domains.md) conditions to control button visibility                            |

**Example - Contextual navigation with form updates:**

```json
{
  "routes": [
    {
      "id": "item.booking.file",
      "label": "Booking form",
      "icon": "library_books",
      "route": "/booking/object.id",
      "context": {
        "entity": "lodging\\sale\\booking\\Booking",
        "view": "form.default",
        "domain": ["id", "=", "object.id"],
        "reset": true
      }
    },
    {
      "id": "item.booking.edit",
      "label": "Booked services",
      "icon": "room_service",
      "route": "/booking/object.id/services"
    },
    {
      "id": "item.booking.contract",
      "label": "Send contract",
      "icon": "drive_file_rename_outline",
      "route": "/booking/object.id/contract",
      "visible": [["has_contract", "=", true], ["status", "=", "confirmed"]]
    }
  ]
}
```

**Example - External document export:**

```json
{
  "routes": [
    {
      "id": "print.pdf",
      "label": "Print tender",
      "icon": "print",
      "route": "/?get=renover_tender-pdf&id=object.id",
      "target": "_blank",
      "absolute": true
    }
  ]
}
```

---

## Keyboard Navigation and Shortcuts

Form views support keyboard interaction for improved accessibility and speed:

**Navigation:**

* Use the `<tab>` key to move between input fields
* In dropdown selection boxes, use `<up>` and `<down>` arrow keys to navigate options

**Actions:**

* While in edit mode, press `<ctrl+s>` to save the current form
* When a split-button has multiple save actions (e.g., save and continue, save and close), the first save action is executed

**Implementation Details:**

Keyboard events are captured by the visible Frame and relayed to the active context, which passes them to the view via a `keyboardAction()` call.

---

## Example Form Configuration

A complete form configuration combining layout, routes, and actions:

```json
{
  "name": "Booking Details",
  "description": "View and edit booking information",
    "routes": [
    {
      "id": "services",
      "label": "Manage Services",
      "icon": "room_service",
      "route": "/booking/object.id/services"
    }
  ],
  "layout": {
    "groups": [
      {
        "id": "main",
        "label": "Booking Information",
        "sections": [
          {
            "id": "overview",
            "label": "Overview",
            "rows": [
              {
                "id": "row_1",
                "columns": [
                  {
                    "id": "col_1",
                    "width": 50,
                    "items": [
                      {
                        "type": "field",
                        "value": "reference",
                        "readonly": true
                      },
                      {
                        "type": "field",
                        "value": "status"
                      }
                    ]
                  },
                  {
                    "id": "col_2",
                    "width": 50,
                    "items": [
                      {
                        "type": "field",
                        "value": "customer_id",
                        "widget": {
                          "fields": ["name", "email"]
                        }
                      }
                    ]
                  }
                ]
              }
            ]
          }
        ]
      }
    ]
  }
}
```

---