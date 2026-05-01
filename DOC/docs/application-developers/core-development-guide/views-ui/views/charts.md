# Charts

Charts are visual components for displaying data aggregations and trends. They enable no-code creation of data visualizations by defining datasets, operations, filters, and date ranges. Charts are typically integrated into [dashboards](./dashboards.md) to provide at-a-glance insights into data.

**Purpose:** Visualize aggregated data in charts and graphs.

**Example:** A pie chart showing user roles distribution, or a bar chart displaying sales trends over time.

**Key Features:**

* Support for multiple chart types (bar, pie, line, area, etc.)
* Flexible data aggregation through operations (count, sum, average, min, max)
* Date range filtering for time-based data analysis
* Domain-based filtering for conditional data inclusion
* Access control for sensitive data visualization
* Multiple datasets in a single chart

---

## Chart Structure

Charts are defined as JSON objects with properties for layout, data aggregation, filtering, and access control.

**Core Properties:**

| **PROPERTY**                           | **TYPE** | **DESCRIPTION**                                                                                                                                                                                                           |
| -------------------------------------- | -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `name`                                 | `string` | Unique identifier for the chart (used in dashboard references)                                                                                                                                                            |
| `description`                          | `string` | (optional) Text description of the chart                                                                                                                                                                                  |
| `access`                               | object   | (optional) List of group IDs allowed to view the chart (e.g., `"groups" = ["group.default.admin", "group.default.manager"]`). If omitted, visible to all authenticated users. Refer to [access control](../../models/authorization/access-control-lists.md) for details |
| `controller`                           | string   | (optional) Custom controller for chart data processing. Defaults to `core_model_chart` if not specified. See [Custom Chart Controllers](../../controllers-routing/controllers.md) for advanced use cases.                                                     |
| [`header`](#header-configuration) | object   | (optional) Header configuration for chart view, including available modes (e.g., `grid`, `chart`) and default mode.                                                                                                       |
| [`layout`](#layout-configuration)      | object   | Chart display configuration, including type and axis definitions (structure varies by chart type)                                                                                                                         |

---

## Header Configuration

The `header` property defines the header configuration for the chart view. It allows you to specify available modes (e.g., `grid`, `chart`) and the default mode when the chart is loaded.

**Header Properties:**

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                        |
| ------------ | -------- | ---------------------------------------------------------------------- |
| `modes`      | array    | List of available modes for the chart view (e.g., `["grid", "chart"]`) |

---

## Layout Configuration

The `layout` property defines how the chart is visually rendered. Configuration depends on the chart type.

**Common Properties:**

| **PROPERTY**     | **TYPE**  | **DESCRIPTION**                                                                                         |
| ---------------- | --------- | ------------------------------------------------------------------------------------------------------- |
| `entity`         | `string`  | (optional) Entity name for field references (e.g., `mypackage\\Model`)                                  |
| `lang`           | `string`  | (optional) Language in which multilang field have to be returned (2 letters ISO 639-1)                  |
| `domain`         | array     | (optional) Domain filters to apply to the data before aggregation (e.g., `[["status", "=", "active"]]`) |
| `stacked`        | `boolean` | (for bar) If `true`, stacks bars on top of each other. Default is `false`                               |
| `group_by`       | `string`  | (optional) Field to group data by (`field`: Polar, Doughnut or Pie charts, `range`: Line or Bar charts) |
| `field`          | `string`  | (for date-based charts) Date field to use for grouping and filtering                                    |
| `range_interval` | `string`  | (for date-based charts) Interval for grouping (e.g., `day`, `month`)                                    |
| `range_from`     | `string`  | (for date-based charts) Start of range (e.g., `date.this.year.first`)                                   |
| `range_to`       | `string`  | (for date-based charts) End of range (e.g., `date.this.year.last`)                                      |
| `datasets`       | array     | List of datasets to display (structure varies by chart type)                                            |
| `mode`           | `string`  | (optional) Mode defining to way data are to be returned                                                 |

**Example Layout:**

```json
{
  ...
  "layout": {
    "entity": "tutorial\\Sales",
    "stacked": false,
    "group_by": "range",
    "field": "date_from",
    "range_interval": "month",
    "range_from": "date.this.year.first",
    "range_to": "date.this.year.last",
    "datasets": [
        ...
    ]
  },
}
```

---

## Dataset Configuration

The `datasets` property defines the data series displayed in the chart. Each series can represent a distinct metric or filtered subset of data.

**Dataset Structure:**

| **PROPERTY** | **TYPE** | **DESCRIPTION**                                                     |
| ------------ | -------- | ------------------------------------------------------------------- |
| `label`      | `string` | Display name for this data series (appears in legend and tooltip)   |
| `operation`  | array    | Aggregation operation to perform (e.g., `["SUM", "object.amount"]`) |
| `domain`     | array    | (optional) Additional domain filters specific to this dataset       |

**Example Dataset:**

```json
{
  "datasets": [
    {
      "label": "Active Users",
      "operation": ["COUNT", "id"],
      "domain": [["is_active", "=", true]],
    }
  ]
}
```

---

## Aggregation Operations

The `operation` property specifies how data is aggregated. Supported operations include:

| **OPERATION** | **DESCRIPTION**                       | **EXAMPLE**                                                                              |
| ------------- | ------------------------------------- | ---------------------------------------------------------------------------------------- |
| `COUNT`       | Count of non-null values in the field | `"operation": ["COUNT", "id"]` - Count records                                           |
| `SUM`         | Sum of numeric values                 | `"operation": ["SUM", "object.amount"]` - Total revenue                                  |
| `AVG`         | Average of numeric values             | `"operation": ["AVG", "object.amount"]` - Average order value                            |
| `MIN`         | Minimum value                         | `"operation": ["MIN", "object.amount"]` - Lowest price                                   |
| `MAX`         | Maximum value                         | `"operation": ["MAX", "object.amount"]` - Highest price                                  |
| `ABS`         | Absolute value                        | `"operation": ["ABS", "object.amount"]` - Absolute revenue                               |
| `DIFF`        | Difference between two fields         | `"operation": ["DIFF", "object.amount", "object.cost_amount"]` - Profit (revenue - cost) |

Operations are applied after [domain](#filtering) conditions are evaluated.

---

## Date Range Format

Date ranges allow time-based filtering using relative date expressions. This enables dynamic charts that always show "this month" or "last quarter" regardless of the current date.

**Format Syntax:**

```
date.[relative_period].[period_type].[boundary]
```

**Components:**

* **relative_period:** `this`, `prev`, or `next`
* **period_type:** `day`, `week`, `month`, `quarter`, `semester`, or `year`
* **boundary:** `first` or `last`

**Examples:**

| **EXPRESSION**            | **MEANING**                            |
| ------------------------- | -------------------------------------- |
| `date.this.month.first`   | First day of the current month         |
| `date.this.month.last`    | Last day of the current month          |
| `date.this.year.first`    | First day of the current year          |
| `date.prev.month.first`   | First day of the previous month        |
| `date.prev.month.last`    | Last day of the previous month         |
| `date.next.quarter.first` | First day of the next quarter          |
| `date.this.week.first`    | First day of the current week (Monday) |
| `date.this.week.last`     | Last day of the current week (Sunday)  |

**Usage with `range_from` and `range_to`:**

```json
{
  "range_interval": "month",
  "range_from": "date.this.year.first",
  "range_to": "date.this.month.last"
}
```

This displays data from January 1st of the current year through the last day of the current month.

---

## Filtering

The `domain` property applies [domain](../../models/domains.md) conditions to filter data before aggregation. This allows charts to visualize only relevant records.

**Single Condition:**

```json
{
  "domain": [["status", "=", "active"]]
}
```

**Multiple Conditions (AND):**

```json
{
  "domain": [
    ["status", "=", "confirmed"],
    ["amount", ">", 1000]
  ]
}
```

**Complex Conditions (OR):**

```json
{
  "domain": [
    ["status", "in", ["pending", "confirmed"]],
    ["is_archived", "=", false]
  ]
}
```

---

## Chart Types and Examples

The chart layout depends on the selected chart type

**Line, Bar** : several datasets, each dataset corresponds to a value for a specific interval : group_by range
**Polar, Doughnut, Pie** : a single dataset, each value corresponds to a segment => group_by field: labels are the groups values, values are the operations on groups

Examples:
```bash
/?get=model_chart&entity=lodging\sale\booking\Booking&group_by=range&range_from=2022-03-01&range_to=2022-12-30&datasets=[{"operation":["SUM","object.price"], "label":"test"}]
```

```bash
/?get=model_chart&entity=lodging\sale\booking\Booking&group_by=field&range_from=2022-03-01&range_to=2022-06-30&datasets=[{"operation":["SUM","object.price"], "label":"test"}]&field=type_id
```
---

## Complete Chart Configuration Example

```json
{
  "name": "Sales Chart",
  "description": "Key sales metrics and trends",
  "access": {
    "groups": ["mypackage.default.user", "mypackage.default.admin"]
  },
  "controller": "core_model_chart",
  "header": {
      "modes": ["grid", "chart"]
  },
  "layout": {
    "entity": "mypackage\\Sales",
    "stacked": false,
    "group_by": "range",
    "field": "date_from",
    "range_interval": "month",
    "range_from": "date.prev.year.first",
    "range_to": "date.this.month.last",
    "datasets": [
      {
        "label": "Revenue",
        "color": "#007bff",
        "operation": ["SUM", "object.amount"],
        "domain": ["status", "=", "completed"]
      },
      {
        "label": "Costs",
        "color": "#dc3545",
        "operation": ["SUM", "object.cost_amount"],
        "domain": ["is_archived", "=", false]
      }
    ]
  },
}
```

This chart displays a bar graph comparing revenue and costs side-by-side for each month over the past year, visible only to sales and admin groups, with archived records excluded.

---

## Displaying Charts in Dashboards

Charts are typically included in [dashboard](./dashboards.md) configurations through the `items` property. Each chart is rendered as a component within the dashboard layout.

Refer to the [dashboard documentation](./dashboards.md) for details on integrating charts into dashboard layouts and configuring chart size and positioning.

---