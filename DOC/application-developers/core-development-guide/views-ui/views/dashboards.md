# Dashboards

Dashboards are control panels that display multiple [views](./common-structures/common-structures.md) (lists, charts, forms) on a single page, enabling aggregated overviews and interactive data monitoring. They allow users to monitor multiple metrics and datasets simultaneously without navigating between different pages.

**Purpose:** Summarize and display data from multiple views or entities in a unified interface.

**Example:** A sales dashboard showing a revenue trend chart, recent orders list, and key metrics, all updated in real-time.

**Key Features:**

* Aggregates multiple sub-contexts and views in a single layout
* Flexible grid-based layout with customizable widths
* Responsive design adapting to different screen sizes
* Domain-based filtering applied to embedded views
* Support for multiple view types: lists, charts, forms, and metrics
* Independent refresh and interaction of embedded views

---

## Dashboard Structure

Dashboards are configured as JSON objects containing an array of dashboard items. The base of their configuration is the `layout` property, in which you can define groups, sections, rows, and columns to organize the dashboard's content. Each item within the layout specifies which view to embed and how to filter it. For more details on the layout structure, refer to the [Forms layout documentation](./forms#layout-structure).

---

## Item Structure

Dashboard items are objects representing embedded views on the dashboard. Each item specifies which view to display, how to position it, and optional filters to apply.

**Item Properties:**

| **PROPERTY**  | **TYPE**  | **DESCRIPTION**                                                                                                           |
| ------------- | --------- | ------------------------------------------------------------------------------------------------------------------------- |
| `id`          | `string`  | Unique identifier for the item (used for translations and internal references)                                            |
| `label`       | `string`  | (optional) Title displayed above the item on the dashboard                                                                |
| `description` | `string`  | (optional) Short explanation of the item's content or purpose (e.g., "Shows active users from the past month")            |
| `width`       | `integer` | (optional) Width as a percentage of the dashboard width (1-100). If omitted, defaults to responsive auto-layout           |
| `height`      | `integer` | (optional) Height as a percentage of the dashboard height (1-100%)                                                        |
| `entity`      | `string`  | Full class name (with namespace) of the entity (e.g., `core\User`, `sales\Order`)                                         |
| `view`        | `string`  | View ID to embed (e.g., `list.default`, `chart.revenue`, `form.overview`)                                                 |
| `domain`      | `array`   | (optional) [Domain](../../models/domains.md) conditions to filter the embedded view (e.g., `[["status", "=", "active"]]`) |

---

## Layout and Positioning

Dashboard items are arranged in a responsive grid layout. The `width` property controls how many columns an item spans.

**Single Row, Two Columns:**

```json
{ "layout": 
    {
        "groups": [
            {
                "sections": [
                    {
                        "rows": [
                            {
                                "columns": [
                                    {
                                        "items": [
                                            {
                                                "id": "item1",
                                                "label": "Active Users",
                                                "width": 50,
                                                "entity": "core\\User",
                                                "view": "chart.metrics"
                                            },
                                            {
                                                "id": "item2",
                                                "label": "Revenue",
                                                "width": 50,
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

**Two Rows, Three Columns (Row 1) + Two Columns (Row 2):**

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
                    "items": [
                      {
                        "id": "item1",
                        "label": "Active Users",
                        "width": 33,
                        "entity": "core\\User",
                        "view": "chart.metrics"
                      }
                    ]
                  },
                  {
                    "items": [
                      {
                        "id": "item2",
                        "label": "Revenue",
                        "width": 33,
                        "entity": "sales\\Sale",
                        "view": "chart.metrics"
                      }
                    ]
                  },
                  {
                    "items": [
                      {
                        "id": "item3",
                        "label": "New Signups",
                        "width": 34,
                        "entity": "core\\User",
                        "view": "chart.metrics"
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
                        "id": "item4",
                        "label": "Recent Orders",
                        "width": 50,
                        "entity": "sales\\Order",
                        "view": "list.default"
                      }
                    ]
                  },
                  {
                    "items": [
                      {
                        "id": "item5",
                        "label": "Top Products",
                        "width": 50,
                        "entity": "inventory\\Product",
                        "view": "list.top_selling"
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

The `domain` property applies [domain](../../models/domains.md) conditions to filter data displayed in embedded views. Filters are applied independently to each item without affecting other dashboard items.

**Single Domain Condition:**

```json
{
  "id": "active_users",
  "label": "Active Users",
  "entity": "core\\User",
  "view": "list.default",
  "domain": [["is_active", "=", true]]
}
```

**Multiple Conditions (AND):**

```json
{
  "id": "recent_sales",
  "label": "Recent Completed Sales",
  "entity": "sales\\Sale",
  "view": "list.default",
  "domain": [
    ["status", "=", "completed"],
    ["created", ">", "2024-01-01"]
  ]
}
```

**Complex Conditions (OR):**

```json
{
  "id": "priority_orders",
  "label": "Priority Orders",
  "entity": "sales\\Order",
  "view": "list.default",
  "domain": [
    ["priority", "in", ["high", "urgent"]],
    ["is_archived", "=", false]
  ]
}
```

---

## Access Control

Dashboard visibility can be restricted through the [access control](../../models/authorization/access-control-lists.md) system. Refer to the [views documentation](./common-structures/common-structures.md) for configuring group-based or user-based access restrictions.

Individual items within a dashboard inherit access restrictions from their referenced views. If a user lacks permissions to view a particular item's view or entity, that item is either hidden or displays an access denied message.

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
                        "view": "list.top_selling"
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
                        "view": "list.default"
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

---