# Widget Rendering

Widgets define how fields are displayed and interacted with in views. They allow customization of the display type, format, and styling for fields without requiring custom code.

**Purpose:** Configure the visual representation and behavior of fields in forms and lists.

**Key Features:**

* Override the default display type for a field (e.g., show datetime as a simple date)
* Apply custom formatting through usage specifications
* Add visual styling through simple, flat CSS-like properties
* Support multiple input variants for relational fields (inline selection, multi-select, etc.)

---

## Widget Configuration

Widgets are specified within an item's configuration and support three customization layers:

* **Type:** Override the default display widget (e.g., `date`, `text`, `select`)
* **Usage:** Apply formatting rules (e.g., `datetime/short`, `amount/money:2`)
* **Styling:** Add visual customizations (colors, spacing, icons, etc.)

---

## Display Types

The `type` property overrides the default widget type determined by the field's data type.

### Widget Types

Widgets come in various types that control the basic display and interaction mode of a field. For a complete list of available widget types, refer to the [Widget Types documentation](./widget-types.md).

**Example - Display datetime as date:**

```json
{
  "type": "field",
  "value": "moment",
  "width": 33,
  "widget": {
    "type": "date"
  }
}
```

### Field-Type-Specific Types

Different field types support additional widget variants:

| **FIELD TYPE** | **WIDGET TYPE** | **DESCRIPTION**                                                                                          |
| -------------- | --------------- | -------------------------------------------------------------------------------------------------------- |
| `boolean`      | `toggle`        | Toggle switch (default)                                                                                  |
| `boolean`      | `checkbox`      | Checkbox input                                                                                           |
| `many2one`     | `select`        | Dropdown with inline selection, typeahead, and options to create or perform advanced selection (default) |
| `one2many`     | `list`          | Table display of related items (default)                                                                 |
| `one2many`     | `multiselect`   | Multi-select input view for bulk selection                                                               |
| `many2many`    | `list`          | Table display of related items (default)                                                                 |
| `many2many`    | `multiselect`   | Multi-select input view for bulk selection                                                               |

**Example - Display one2many as multi-select:**

```json
{
  "type": "field",
  "value": "services",
  "widget": {
    "type": "multiselect"
  }
}
```

---

## Widget Properties

Widgets support a variety of properties that control their behavior and appearance. These properties can be combined to create rich, interactive field displays.

### Common Widget Properties
| **PROPERTY** | **TYPE**  | **DESCRIPTION**                                                                             |
| ------------ | --------- | ------------------------------------------------------------------------------------------- |
| `value`      | `string`  | The field name or value to display (e.g., `created`, `total_amount`)                        |
| `link`       | `boolean` | If `true`, the item content is displayed as a clickable link                                |
| `type`       | `string`  | Overrides the default display type based on the field type (e.g., `text`, `select`, `date`) |
| `usage`      | `string`  | Overrides the field's [usage](../../../models/entities/fields.md#usages) to change display formatting                            |

#### Relational Field Widgets

For one2many and many2many fields, additional properties customize how related objects are displayed:

For many2one fields, since the display involves the name, an object is requested (instead of simply the id). Additional sub-fields can be specified using dot notation for nested properties (e.g., `object.target_id.target_field`).

For further documentation, since most fields depend on the view type, see the [forms](../forms.md) and [lists](../lists.md) documentation for relational field widget examples and specific properties.

## Usage Formatting

The `usage` property applies formatting rules to field values based on predefined or custom usage specifications. This is useful for displaying dates in specific formats, currencies with proper symbols, or other specialized representations.

**Common Usages:**

* `datetime/short` - Short datetime format
* `datetime/long` - Long datetime format
* `date/short` - Short date format
* `amount/money:2` - Currency with 2 decimal places
* `numeric/integer` - Integer formatting
* `numeric/decimal` - Decimal number formatting
* `color` - Color picker or color display
* `icon` - Icon selector or display
* `url` - URL with link rendering

For a complete list of available usages, refer to [content-type specifications](../../../models/entities/fields.md#common-usages).

**Example - Display date with short format:**

```json
{
  "type": "field",
  "value": "issue_date",
  "width": 15,
  "widget": {
    "usage": "datetime/short"
  }
}
```

**Example - Display amount as formatted currency:**

```json
{
  "type": "field",
  "value": "total_price",
  "width": 20,
  "widget": {
    "usage": "amount/money:2"
  }
}
```

---

## Styling

Widgets support simple, built-in styling properties that control visual appearance without requiring custom CSS. These properties follow consistent naming conventions and can be combined to create diverse visual effects.

### Supported Style Properties

| **PROPERTY**       | **TYPE** | **DESCRIPTION**                                                 |
| ------------------ | -------- | --------------------------------------------------------------- |
| `text_color`       | `string` | Text color (CSS color name or hex code)                         |
| `text_weight`      | `string` | Font weight (e.g., `bold`, `500`, `700`)                        |
| `text_width`       | `string` | Text width (CSS syntax, e.g., `100%`, `200px`)                  |
| `text_align`       | `string` | Horizontal text alignment (`left`, `center`, `right`)           |
| `text_padding`     | `string` | Inner spacing around text (CSS syntax, e.g., `4px`, `0.25rem`)  |
| `text_decoration`  | `string` | Text decoration (e.g., `underline`, `overline`, `line-through`) |
| `background_color` | `string` | Background color (CSS color name or hex code)                   |
| `border_color`     | `string` | Border color (CSS color name or hex code)                       |
| `border_radius`    | `string` | Border corner radius (CSS syntax, e.g., `4px`, `0.25rem`)       |

### Color Values

Colors can be specified as:

* CSS color names: `red`, `blue`, `orange`, `lightgray`
* Hexadecimal codes: `#333`, `#b30000`, `#fff3cd`
* RGB/RGBA notation: `rgb(255, 0, 0)`, `rgba(255, 0, 0, 0.5)`
* HSL/HSLA notation: `hsl(0, 100%, 50%)`, `hsla(0, 100%, 50%, 0.5)`

### Conditional Styling

Styling supports two modes of application:

1.  **Static Styling**: Apply fixed styles that do not change based on field values or context.
2.  **Dynamic Styling**: Use conditional logic in the view configuration to apply different styles based on field values or other conditions (e.g., highlight overdue tasks in red).

This allows for resolution of styles directly in the view configuration. This way, widgets never need to be aware of:

* The domain
* The user
* The object state

Furthermore, this allows a default style to be applied, which can then be overloaded by a more specific style when certain conditions are met.

**Static Styling:**

```json
"widget": {
  "styles": {
    "text_align": "center",
    "background_color": "#dcfce7",
    "border_radius": "5px"
  }
}
```

Here the widget will always have centered text, a light green background, and rounded corners regardless of any conditions.

**Dynamic Styling:**

```json
"widget": {
  "styles": [
    {
      "apply": {
        "background_color": "#dcfce7"
      }
    },
    {
      "visible": ["status", "=", "cancelled"],
      "apply": {
        "background_color": "#f5b5b5"
      }
    }
  ]
}
```

Here the widget will have a default light green background, but if the `status` field equals `cancelled`, the background will change to light red.



---

## Widget in Different View Contexts

While the core widget configuration is consistent, the context in which widgets appear affects their behavior:

**In Views:**

In views, widgets control the display of fields within forms, lists, and other view types. The available widget types and properties may vary based on the view context (e.g., some widgets may only be applicable in forms).

**In Relational Fields:**

For one2many and many2many fields, widget configuration affects how the related object list is displayed. Refer to the [forms documentation](../forms.md) for relational field widget examples.

---

## Complete Widget Configuration Example

```json
{
  "name": "Order Details Form",
  "layout": {
    "groups": [
      {
        "id": "main",
        "sections": [
          {
            "id": "order_info",
            "rows": [
              {
                "columns": [
                  {
                    "items": [
                      {
                      "type": "field",
                      "value": "status",
                      "width": "10%",
                      "readonly": true,
                      "widget": {
                      "styles": [
                        {
                            "apply": {
                                "text_weight": "500",
                                "text_align": "center",
                                "text_padding": "4px",
                                "border_radius": "5px"
                                }
                        },
                        {
                            "visible": ["integrated", "=", "object.status"],
                            "apply": {
                                "background_color": "#dcfce7"
                            }
                        },
                        {
                            "visible": [["assigned", "completed", "validated"], "contains", "object.status"],
                            "apply": {
                                "background_color": "#f5e4b5"
                            }
                        },
                        {
                            "visible": [["created", "cancelled"], "contains", "object.status"],
                            "apply": {
                                "background_color": "#f5b5b5"
                            }
                        }]
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