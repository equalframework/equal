# Dashboards

Dashboards are control panels that display multiple [views](./views.md) (lists, charts, forms) on a single page, enabling aggregated overviews and interactive data monitoring. They provide compact, read-only entry points to operational data and surface the most relevant information for a specific user context without exposing the full management interface of the underlying entities.

**Purpose:** Summarize and display data from multiple views or entities in a unified interface.

**Example:** A sales dashboard showing a revenue trend chart, recent orders list, and key metrics, all updated in real time.

**Key Features:**

* Aggregates multiple sub-contexts and views in a single layout
* Flexible grid-based layout with customizable widths and heights
* Responsive design adapting to different screen sizes
* Domain-based filtering applied to embedded views
* Support for multiple view types: lists, charts, forms, and metrics
* Independent refresh and interaction of embedded views

---

## File Naming

A dashboard view is defined as a JSON view file using the standard view naming convention:

```text
Entity.dashboard.variant.json
```

Examples:

```text
Owner.dashboard.default.json
Document.dashboard.default.json
Payment.dashboard.default.json
```

The view identifier used by contexts and embedded views is then `dashboard.variant`, for example `dashboard.default`.

---

## Dashboard Structure

Dashboards are configured as JSON objects containing a nested layout. The main configuration is placed under `layout.groups`, where groups, sections, rows, columns, and items organize the dashboard content. Each item specifies which view to embed and how to filter it.

For more details on the shared form-style layout structure, refer to the [Forms layout documentation](./forms#layout-structure).

The dashboard layout hierarchy is:

```text
layout
+-- groups
    +-- sections
        +-- rows
            +-- columns
                +-- items
```

Each level controls part of the visual structure:

| Level | Purpose |
| --- | --- |
| `groups` | Main dashboard blocks. |
| `sections` | Logical areas inside a group. |
| `rows` | Horizontal bands. |
| `columns` | Vertical areas inside a row. |
| `items` | Embedded widgets displayed inside a column. |

A minimal dashboard view usually contains:

```json
{
  "name": "Main dashboard",
  "description": "",
  "layout": {
    "groups": [
      {
        "id": "group.main",
        "label": "Main",
        "height": "100%",
        "sections": [
          {
            "rows": [
              {
                "height": "100%",
                "columns": [
                  {
                    "width": "100%",
                    "items": [
                      {
                        "id": "item.records",
                        "label": "Records",
                        "description": "",
                        "width": "100%",
                        "entity": "my\\module\\Entity",
                        "view": "list.dashboard"
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

Widths and heights are generally expressed as CSS-like percentage strings. The total width of columns in a row should normally add up to `100%`.

---

## Item Structure

Dashboard items are objects representing embedded views on the dashboard. Each item specifies which view to display, how to position it, and optional filters to apply.

**Item Properties:**

| **PROPERTY**  | **TYPE**  | **DESCRIPTION** |
| ------------- | --------- | --------------- |
| `id` | `string` | Unique identifier for the item, used for translations and internal references. |
| `label` | `string` | Optional title displayed above the item on the dashboard. |
| `description` | `string` | Optional short explanation of the item's content or purpose. |
| `width` | `string` or `integer` | Optional width as a percentage of the containing column, for example `"50%"` or `50`. |
| `height` | `string` or `integer` | Optional height as a percentage or CSS-like size, for example `"50%"` or `"320px"`. |
| `entity` | `string` | Full class name of the target entity, for example `core\User` or `sales\Order`. |
| `view` | `string` | View ID to embed, for example `list.dashboard`, `chart.revenue`, or `form.dashboard`. |
| `domain` | `array` | Optional [domain](../../core-development/data-rules-processing/domains.md) conditions to filter the embedded view. |

The `view` property can reference any compatible view, but dashboard items commonly use compact variants such as:

* `list.dashboard`
* `form.dashboard`
* `form.error`
* `chart.default`
* another custom list, form, or chart variant

---

## Layout and Positioning

Dashboard items are arranged in a responsive grid layout. The `width` property controls how much horizontal space an item or column occupies.

**Single Row, Two Columns:**

```json
{
  "layout": {
    "groups": [
      {
        "sections": [
          {
            "rows": [
              {
                "columns": [
                  {
                    "width": "50%",
                    "items": [
                      {
                        "id": "item1",
                        "label": "Active Users",
                        "width": "100%",
                        "entity": "core\\User",
                        "view": "chart.metrics"
                      }
                    ]
                  },
                  {
                    "width": "50%",
                    "items": [
                      {
                        "id": "item2",
                        "label": "Revenue",
                        "width": "100%",
                        "entity": "sales\\Sale",
                        "view": "chart.metrics"
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

## Filtering

The `domain` property applies [domain](../../core-development/data-rules-processing/domains.md) conditions to filter data displayed in embedded views. Filters are applied independently to each item without affecting other dashboard items.

**Single Domain Condition:**

```json
{
  "id": "active_users",
  "label": "Active Users",
  "entity": "core\\User",
  "view": "list.dashboard",
  "domain": ["is_active", "=", true]
}
```

**Multiple Conditions:**

```json
{
  "id": "recent_sales",
  "label": "Recent Completed Sales",
  "entity": "sales\\Sale",
  "view": "list.dashboard",
  "domain": [
    ["status", "=", "completed"],
    ["created", ">", "2024-01-01"]
  ]
}
```

Domains may use runtime context values that are resolved by the framework, such as:

* `user.id`
* `user.employee_id`
* `user.ownerships_ids`
* `user.condos_ids`
* `object.id`

---

## Embedded List Dashboard Views

When a dashboard item references `list.dashboard`, create the corresponding list view separately if it does not already exist. Dashboard list variants are usually compact and expose only the fields and row actions needed for the dashboard.

Example:

```json
{
  "name": "Pending payments",
  "description": "This view displays pending payments.",
  "header": {
    "layout": "inline",
    "actions": false,
    "exports": false,
    "advanced_search": false
  },
  "order": "due_date",
  "sort": "desc",
  "domain": ["remaining_amount", "<>", 0],
  "on_empty": {
    "message": "No pending payment."
  },
  "routes": [
    {
      "id": "route.detail",
      "label": "Details",
      "inline": true,
      "icon": "visibility",
      "context": {
        "entity": "my\\module\\Entity",
        "view": "form.dashboard",
        "domain": [
          ["id", "=", "object.id"]
        ]
      }
    }
  ],
  "layout": {
    "interactions": {
      "click": false
    },
    "items": [
      {
        "type": "field",
        "value": "name",
        "width": "50%"
      },
      {
        "type": "field",
        "value": "status",
        "width": "50%"
      }
    ]
  }
}
```

Useful options for dashboard lists:

* `header.layout`: often set to `"inline"` for compact rendering.
* `header.actions`: can be `false` to hide default actions.
* `header.exports`: can be `false` to hide export options.
* `header.advanced_search`: can be `false` to hide advanced search.
* `domain`: default filter for the list.
* `order` and `sort`: default ordering.
* `on_empty.message`: message displayed when the list has no result.
* `routes`: inline navigation actions from each row.
* `layout.interactions.click`: can disable row click behavior.
* `layout.items`: fields displayed as columns.

---

## Embedded Form Dashboard Views

When a dashboard item or route references `form.dashboard`, create the corresponding form view separately if it does not already exist. Dashboard form variants are usually compact and read-only.

Example:

```json
{
  "name": "Record details",
  "description": "",
  "header": {
    "layout": "inline",
    "actions": false
  },
  "layout": {
    "groups": [
      {
        "sections": [
          {
            "rows": [
              {
                "columns": [
                  {
                    "width": "100%",
                    "items": [
                      {
                        "type": "field",
                        "value": "name",
                        "width": "100%",
                        "readonly": true
                      },
                      {
                        "type": "field",
                        "value": "status",
                        "width": "50%"
                      },
                      {
                        "type": "field",
                        "value": "created",
                        "width": "50%"
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

Useful options for dashboard forms:

* `header.actions`: can disable record actions.
* `header.exports`: can disable export actions.
* `header.advanced_search`: can disable advanced search.
* `readonly`: can make a field non-editable.
* `visible`: can conditionally show or hide fields or sections.
* `widget`: can configure the rendering of a field.
* `type: "field"` displays a model field.
* `type: "label"` displays static text.

---

## Access Control

Dashboard visibility can be restricted through the [access control](../../core-development/security-access/access-control-lists.md) system. Refer to the [views documentation](./views.md) for configuring group-based or user-based access restrictions.

Individual items within a dashboard inherit access restrictions from their referenced views. If a user lacks permissions to view a particular item's view or entity, that item is either hidden or displays an access denied message.

---

## Creation Checklist

To create a dashboard from scratch:

1. Create the main `Entity.dashboard.default.json` file.
2. Define `layout.groups`, then sections, rows, columns, and items.
3. For each item, choose the target `entity` and `view`.
4. Add item-level `domain` filters when the widget should only display contextual data.
5. Create the referenced `list.dashboard`, `form.dashboard`, or `chart.*` views if they do not already exist.
6. Keep dashboard views compact by disabling unnecessary actions, exports, advanced search, and direct editing.
7. Add `routes` in list dashboard views when users need to open a compact detail view.
8. Add `on_empty.message` for user-facing dashboard blocks that may legitimately have no records.

---

## Complete Dashboard Examples

### Sales and Revenue Dashboard

```json
{
  "id": "sales_dashboard",
  "name": "Sales Dashboard",
  "description": "Overview of sales performance and key metrics",
  "layout": {
    "groups": [
      {
        "label": "Sales Overview",
        "sections": [
          {
            "rows": [
              {
                "columns": [
                  {
                    "items": [
                      {
                        "id": "revenue_chart",
                        "label": "Revenue Trend",
                        "entity": "sales\\Sale",
                        "view": "chart.revenue",
                        "domain": [["created", ">", "2024-01-01"]]
                      }
                    ]
                  },
                  {
                    "items": [
                      {
                        "id": "top_products",
                        "label": "Top Selling Products",
                        "entity": "inventory\\Product",
                        "view": "list.dashboard"
                      }
                    ]
                  }
                ]
              },
              {
                "columns": [
                  {
                    "items": [
                      {
                        "id": "recent_orders",
                        "label": "Recent Orders",
                        "entity": "sales\\Order",
                        "view": "list.dashboard"
                      }
                    ]
                  },
                  {
                    "items": [
                      {
                        "id": "customer_metrics",
                        "label": "Customer Metrics",
                        "entity": "core\\User",
                        "view": "chart.metrics"
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
