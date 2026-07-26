# Charts

Charts are visual components used to display aggregated data, trends, comparisons, and distributions.

In eQual, chart views are defined declaratively through JSON view schemas. They are rendered on the frontend with **Chart.js** and usually rely on the `core_model_chart` controller to compute aggregated values from ORM entities.

Charts are typically used in dashboards to provide at-a-glance insights into operational or business data, such as turnover, bookings, document sizes, server usage, or record distributions.

## Purpose

Chart views allow developers and Product Owners to create data visualizations without writing a dedicated frontend component for each metric.

A chart view defines:

* the source entity;
* the grouping strategy;
* the date range, if applicable;
* one or more datasets;
* the aggregation operation for each dataset;
* optional filters;
* the Chart.js rendering type.

The chart controller then retrieves matching objects, groups them, computes the requested operations, and returns a structure that can be rendered either as a chart or as a grid.

## General Structure

A chart view is a standard eQual view with a `layout` section dedicated to chart configuration.

```json
{
  "name": "Total turnover of bookings",
  "description": "This view displays the total turnover of the bookings.",
  "access": {
    "groups": ["booking.default.user"]
  },
  "controller": "core_model_chart",
  "header": {
    "modes": ["chart", "grid"]
  },
  "layout": {
    "entity": "sale\\booking\\Booking",
    "type": "bar",
    "stacked": false,
    "group_by": "range",
    "field": "date_from",
    "range_interval": "month",
    "range_from": "date.this.year.first",
    "range_to": "date.this.year.last",
    "datasets": [
      {
        "label": "Turnover excl. VAT",
        "operation": ["SUM", "object.total"],
        "domain": ["status", "<>", "quote"]
      },
      {
        "label": "Turnover incl. VAT",
        "operation": ["SUM", "object.price"],
        "domain": ["status", "<>", "quote"]
      }
    ]
  }
}
```

## Rendering Flow

When a chart view is initialized, the frontend creates a `LayoutChart` instance.

The frontend starts with default layout values, then merges them with the values defined in the view schema.

Default values are:

```json
{
  "type": "bar",
  "stacked": false,
  "group_by": "range",
  "field": "created",
  "range_interval": "month",
  "range_from": "date.this.year.first",
  "range_to": "date.this.year.last"
}
```

The frontend then parses each dataset. If a dataset contains a `domain`, that domain is parsed using the current user and environment context before being sent to the backend.

The frontend calls the configured controller, usually `core_model_chart`, with parameters similar to the following:

```json
{
  "get": "core_model_chart",
  "type": "bar",
  "entity": "sale\\booking\\Booking",
  "group_by": "range",
  "field": "date_from",
  "range_interval": "month",
  "range_from": "2026-01-01T00:00:00.000Z",
  "range_to": "2026-12-31T00:00:00.000Z",
  "datasets": [
    {
      "label": "Turnover excl. VAT",
      "operation": ["SUM", "object.total"],
      "domain": ["status", "<>", "quote"]
    }
  ],
  "mode": "chart"
}
```

The controller returns either:

* a chart-compatible result when the current mode is `chart`;
* a tabular result when the current mode is `grid`.

## Chart.js Integration

eQual chart views are rendered with **Chart.js** on the frontend.

The `layout.type` property is passed to Chart.js as the chart type. Therefore, the chart types supported by eQual depend primarily on the chart types supported by the Chart.js version used by the frontend.

However, the fact that a chart type is supported by Chart.js does not automatically mean that it will work correctly with the default `core_model_chart` response format.

The default controller returns a generic structure based on labels, datasets and legends:

```json
{
  "labels": [],
  "datasets": [],
  "legends": []
}
```

This structure works naturally for charts where each dataset is represented as a simple array of values, such as `bar`, `line`, `pie`, `doughnut`, and `polarArea`.

Some Chart.js chart types, such as `scatter` and `bubble`, usually expect point-based objects rather than simple numeric arrays. These types may require a dedicated controller or an adaptation of the frontend mapping logic.

## Chart Types

The current frontend implementation explicitly handles the following segment-based chart types:

```json
["pie", "doughnut", "polarArea"]
```

These chart types are rendered with a shared color palette and are typically used with `group_by: "field"`.

All other chart types are rendered through the generic Chart.js configuration with `x` and `y` scales.

Common Chart.js types that may be used in eQual chart views include:

| Type        | Typical usage                                            | Current support notes                                                                                         |
| ----------- | -------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- |
| `bar`       | Compare values by category or by time interval.          | Works naturally with the default `core_model_chart` response format. Supports `stacked: true`.                |
| `line`      | Display trends over time.                                | Works naturally with `group_by: "range"`.                                                                     |
| `scatter`   | Display relationships between numerical values.          | Supported by Chart.js, but may require adapted data mapping because Chart.js usually expects `{x, y}` points. |
| `bubble`    | Display relationships with an additional size dimension. | Supported by Chart.js, but usually requires `{x, y, r}` point objects.                                        |
| `radar`     | Compare several values on a radial scale.                | Supported by Chart.js, but not specifically configured in the current implementation.                         |
| `pie`       | Display proportions by category.                         | Explicitly handled as a segment-based chart.                                                                  |
| `doughnut`  | Display proportions by category.                         | Explicitly handled as a segment-based chart.                                                                  |
| `polarArea` | Display category-based radial values.                    | Explicitly handled as a segment-based chart.                                                                  |

Recommended practical usage:

| Use case                                | Recommended type            |
| --------------------------------------- | --------------------------- |
| Monthly turnover                        | `bar` or `line`             |
| Daily resource usage                    | `line` or `bar`             |
| Distribution by status                  | `pie` or `doughnut`         |
| Distribution by related entity          | `bar`, `pie`, or `doughnut` |
| Comparison of several metrics over time | `bar` or `line`             |
| Tabular analysis of aggregated data     | Use `mode: "grid"`          |

## Grouping Modes

Chart views support two main grouping strategies:

```json
"group_by": "range"
```

and:

```json
"group_by": "field"
```

## Range-Based Grouping

When `group_by` is set to `range`, the controller groups objects by time intervals.

This mode is used for charts such as:

* sales per month;
* bookings per week;
* documents created per year;
* average server CPU usage per day.

Example:

```json
{
  "group_by": "range",
  "field": "date_from",
  "range_interval": "month",
  "range_from": "date.this.year.first",
  "range_to": "date.this.year.last"
}
```

In this case, `field` is used as the date field for grouping.

When `group_by` is `range`, the controller initializes all intervals between `range_from` and `range_to`. This means that intervals with no matching data are still present in the result, usually with `null` values for the corresponding datasets.

Supported intervals are:

| Interval | Result label format | Description          |
| -------- | ------------------- | -------------------- |
| `day`    | `YYYY-MM-DD`        | One group per day.   |
| `week`   | `YYYY-WW`           | One group per week.  |
| `month`  | `YYYY-MM`           | One group per month. |
| `year`   | `YYYY`              | One group per year.  |

Example response:

```json
{
  "labels": ["2026-01", "2026-02", "2026-03"],
  "datasets": [
    [1200, 1800, 2100]
  ],
  "legends": ["Turnover"]
}
```

## Field-Based Grouping

When `group_by` is set to `field`, the controller groups objects by the value of a field.

This mode is used for charts such as:

* turnover by customer;
* number of records by status;
* disk usage by server;
* documents by type.

Example:

```json
{
  "group_by": "field",
  "field": "server_id",
  "range_field": "created",
  "range_from": "date.this.day",
  "range_to": "date.this.day"
}
```

In this example:

* `field` is the grouping field;
* `range_field` is the date or datetime field used to filter objects by date;
* records are grouped by `server_id`;
* only records created during the selected date range are included.

When grouping by a `many2one` field, the controller reads both `id` and `name` from the related object. The related object ID is used as the internal group key, while the related object name is used as the display label.

Some existing chart definitions use a direct field name as `group_by`, for example:

```json
{
  "group_by": "parent_id"
}
```

This is functionally a field grouping shortcut. Prefer the explicit `group_by: "field"` plus `field` form for new views unless nearby package conventions already use the shortcut.

## Date Range Filtering

Date range filtering is controlled with:

```json
"range_from": "date.this.year.first",
"range_to": "date.this.year.last"
```

The date field used for filtering is determined as follows:

| Case                                              | Date field used                    |
| ------------------------------------------------- | ---------------------------------- |
| `group_by: "range"` and no `range_field`          | `field`                            |
| `group_by: "range"` and `range_field` is provided | `range_field`                      |
| `group_by: "field"` and `range_field` is provided | `range_field`                      |
| `group_by: "field"` and no `range_field`          | No date range filtering is applied |

The `range_field` must be a valid field of the target entity and must be of type `date` or `datetime`.

For `date` fields, the end date is inclusive.

For `datetime` fields, the controller internally extends `range_to` to the next day and applies an exclusive `<` comparison. This allows all records from the selected end day to be included without requiring the time to be set to the end of the day.

## Relative Date Expressions

The frontend supports relative date expressions through `DateReference`.

Examples:

| Expression              | Meaning                          |
| ----------------------- | -------------------------------- |
| `date.this.day`         | Current day.                     |
| `date.this.week.first`  | First day of the current week.   |
| `date.this.week.last`   | Last day of the current week.    |
| `date.this.month.first` | First day of the current month.  |
| `date.this.month.last`  | Last day of the current month.   |
| `date.this.year.first`  | First day of the current year.   |
| `date.this.year.last`   | Last day of the current year.    |
| `date.prev.month.first` | First day of the previous month. |
| `date.prev.month.last`  | Last day of the previous month.  |
| `date.next.month.first` | First day of the next month.     |
| `date.next.month.last`  | Last day of the next month.      |

Example:

```json
{
  "range_interval": "month",
  "range_from": "date.this.year.first",
  "range_to": "date.this.year.last"
}
```

This configuration displays values grouped by month for the current year.

## Datasets

The `datasets` property defines the values displayed in the chart.

Each dataset represents one metric.

Example:

```json
{
  "label": "Average CPU usage",
  "operation": ["AVG", "object.cpu_use"],
  "domain": ["server_id", "<>", null]
}
```

A chart can contain one or more datasets.

Example with multiple datasets:

```json
{
  "datasets": [
    {
      "label": "Disk usage",
      "operation": ["AVG", "object.dsk_use"],
      "domain": ["server_id", "<>", null]
    },
    {
      "label": "CPU usage",
      "operation": ["AVG", "object.cpu_use"],
      "domain": ["server_id", "<>", null]
    },
    {
      "label": "RAM usage",
      "operation": ["AVG", "object.ram_use"],
      "domain": ["server_id", "<>", null]
    }
  ]
}
```

Each dataset may define its own `domain`. This dataset-specific domain is merged with the global domain before the search is executed.

## Operations

The `operation` property defines the aggregation to compute on each group of objects.

Examples:

```json
["COUNT", "object.id"]
["SUM", "object.total"]
["AVG", "object.cpu_use"]
["MIN", "object.price"]
["MAX", "object.price"]
```

Operations may also contain expressions:

```json
["SUM", ["/", "object.size", 1000]]
```

The controller detects references to object fields using the `object.field_name` syntax and reads the required fields from the ORM.

The exact set of supported operations depends on the backend `Operation` class.

Common operations include:

| Operation | Description                             | Example                     |
| --------- | --------------------------------------- | --------------------------- |
| `COUNT`   | Counts records or non-null values.      | `["COUNT", "object.id"]`    |
| `SUM`     | Computes the sum of numeric values.     | `["SUM", "object.total"]`   |
| `AVG`     | Computes the average of numeric values. | `["AVG", "object.cpu_use"]` |
| `MIN`     | Returns the minimum value.              | `["MIN", "object.price"]`   |
| `MAX`     | Returns the maximum value.              | `["MAX", "object.price"]`   |

## Domains and Filtering

Chart data can be filtered using eQual domains.

There are two levels of filtering:

1. a global domain, applied to all datasets;
2. a dataset-specific domain, applied only to one dataset.

A dataset-specific domain is merged with the global domain.

Example:

```json
{
  "domain": ["status", "<>", "cancelled"],
  "datasets": [
    {
      "label": "Confirmed bookings",
      "operation": ["SUM", "object.price"],
      "domain": ["status", "=", "confirmed"]
    },
    {
      "label": "Pending bookings",
      "operation": ["SUM", "object.price"],
      "domain": ["status", "=", "pending"]
    }
  ]
}
```

This allows several filtered metrics to be displayed in the same chart.

## Chart Mode and Grid Mode

Chart views can be displayed in two modes:

| Mode    | Description                              |
| ------- | ---------------------------------------- |
| `chart` | Displays a Chart.js canvas.              |
| `grid`  | Displays the aggregated data as a table. |

The available modes can be defined in the view header:

```json
{
  "header": {
    "modes": ["chart", "grid"]
  }
}
```

When the mode is `chart`, the controller returns a structure like this:

```json
{
  "labels": ["2026-01", "2026-02"],
  "datasets": [
    [1200, 1800],
    [1452, 2178]
  ],
  "legends": [
    "Turnover excl. VAT",
    "Turnover incl. VAT"
  ]
}
```

When the mode is `grid`, the controller converts the same result into rows:

```json
[
  {
    "#label": "Turnover excl. VAT",
    "2026-01": 1200,
    "2026-02": 1800
  },
  {
    "#label": "Turnover incl. VAT",
    "2026-01": 1452,
    "2026-02": 2178
  }
]
```

The grid mode is useful for checking aggregated values, debugging charts, or presenting data in a more precise tabular form.

## Configuration Options

| Option                  | Location                | Type            | Default                                               | Possible values                                                                                                            | Description                                                                                                                        |
| ----------------------- | ----------------------- | --------------- | ----------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| `name`                  | root                    | `string`        | —                                                     | Any string                                                                                                                 | Name of the chart view. Usually used as the view identifier.                                                                       |
| `description`           | root                    | `string`        | —                                                     | Any string                                                                                                                 | Optional human-readable description of the chart.                                                                                  |
| `access`                | root                    | `object`        | —                                                     | `{ "groups": [...] }`                                                                                                      | Restricts access to specific user groups.                                                                                          |
| `controller`            | root                    | `string`        | `core_model_chart`                                    | Any compatible controller                                                                                                  | Controller used to compute chart data.                                                                                             |
| `header.modes`          | root                    | `array`         | —                                                     | `["chart"]`, `["grid"]`, `["chart", "grid"]`                                                                               | Defines the display modes available to the user.                                                                                   |
| `layout.entity`         | layout                  | `string`        | —                                                     | Fully qualified entity class name                                                                                          | Entity used as the data source. Required by `core_model_chart`.                                                                    |
| `layout.type`           | layout                  | `string`        | `bar`                                                 | Any chart type supported by Chart.js, commonly `bar`, `line`, `scatter`, `bubble`, `radar`, `pie`, `doughnut`, `polarArea` | Chart.js chart type used by the frontend. Some types may require a controller response structure adapted to Chart.js expectations. |
| `layout.stacked`        | layout                  | `boolean`       | `false`                                               | `true`, `false`                                                                                                            | Enables stacked x/y scales for axis-based charts. Mostly relevant for bar charts.                                                  |
| `layout.group_by`       | layout                  | `string`        | `range`                                               | `range`, `field`                                                                                                           | Defines whether data is grouped by time interval or by field value.                                                                |
| `layout.field`          | layout                  | `string`        | `created`                                             | Any valid entity field                                                                                                     | Main grouping field. For `range`, usually a date or datetime field. For `field`, the field whose values are used as groups.        |
| `layout.range_field`    | layout                  | `string`        | Depends on `group_by`                                 | Date or datetime field                                                                                                     | Date field used for date range filtering. Defaults to `field` when `group_by` is `range`. Optional when `group_by` is `field`.     |
| `layout.range_interval` | layout                  | `string`        | `month`                                               | `day`, `week`, `month`, `year`                                                                                             | Time bucket size when grouping by range.                                                                                           |
| `layout.range_from`     | layout                  | `string` / date | `date.this.year.first`                                | Date or relative date expression                                                                                           | Start of the date range.                                                                                                           |
| `layout.range_to`       | layout                  | `string` / date | `date.this.year.last`                                 | Date or relative date expression                                                                                           | End of the date range.                                                                                                             |
| `layout.domain`         | layout / request params | `array`         | `[]`                                                  | eQual domain                                                                                                               | Global filter applied before dataset filters.                                                                                      |
| `layout.datasets`       | layout                  | `array`         | `[]`                                                  | Dataset descriptors                                                                                                        | List of metrics to compute and display.                                                                                            |
| `dataset.label`         | dataset                 | `string`        | `#value` backend fallback / `label` frontend fallback | Any string                                                                                                                 | Label shown in chart legend, tooltip, or grid first column.                                                                        |
| `dataset.operation`     | dataset                 | `array`         | `["COUNT", "object.id"]` frontend fallback            | Operation expression                                                                                                       | Aggregation operation to compute.                                                                                                  |
| `dataset.domain`        | dataset                 | `array`         | —                                                     | eQual domain                                                                                                               | Additional filter specific to this dataset.                                                                                        |
| `mode`                  | request parameter       | `string`        | `chart`                                               | `chart`, `grid`                                                                                                            | Controls whether the controller returns chart data or grid rows.                                                                   |

## Range-Based Chart Example

```json
{
  "name": "Total turnover of bookings",
  "description": "This view displays the total turnover of bookings by month.",
  "access": {
    "groups": ["booking.default.user"]
  },
  "controller": "core_model_chart",
  "header": {
    "modes": ["chart", "grid"]
  },
  "layout": {
    "entity": "sale\\booking\\Booking",
    "type": "bar",
    "stacked": false,
    "group_by": "range",
    "field": "date_from",
    "range_interval": "month",
    "range_from": "date.this.year.first",
    "range_to": "date.this.year.last",
    "datasets": [
      {
        "label": "Turnover excl. VAT",
        "operation": ["SUM", "object.total"],
        "domain": ["status", "<>", "quote"]
      },
      {
        "label": "Turnover incl. VAT",
        "operation": ["SUM", "object.price"],
        "domain": ["status", "<>", "quote"]
      }
    ]
  }
}
```

This chart groups bookings by month, excludes quotes, and displays two datasets: turnover excluding VAT and turnover including VAT.

## Field-Based Chart Example

```json
{
  "name": "Server status",
  "access": {
    "groups": ["admins"]
  },
  "controller": "core_model_chart",
  "header": {
    "modes": ["chart", "grid"]
  },
  "layout": {
    "entity": "infra\\server\\Status",
    "type": "bar",
    "stacked": false,
    "group_by": "field",
    "field": "server_id",
    "range_field": "created",
    "range_from": "date.this.day",
    "range_to": "date.this.day",
    "datasets": [
      {
        "label": "Disk usage",
        "operation": ["AVG", "object.dsk_use"],
        "domain": ["server_id", "<>", null]
      },
      {
        "label": "CPU usage",
        "operation": ["AVG", "object.cpu_use"],
        "domain": ["server_id", "<>", null]
      },
      {
        "label": "RAM usage",
        "operation": ["AVG", "object.ram_use"],
        "domain": ["server_id", "<>", null]
      }
    ]
  }
}
```

This chart groups server status records by server and filters records to the current day using the `created` field.

## Document Size Example

```json
{
  "name": "Documents size",
  "description": "This view displays the total size of documents created during the current year.",
  "access": {
    "groups": ["documents.default.user"]
  },
  "controller": "core_model_chart",
  "header": {
    "modes": ["chart", "grid"]
  },
  "layout": {
    "entity": "documents\\Document",
    "type": "bar",
    "group_by": "range",
    "field": "created",
    "range_interval": "year",
    "range_from": "date.this.year.first",
    "range_to": "date.this.year.last",
    "datasets": [
      {
        "label": "Document size in KB",
        "operation": ["SUM", ["/", "object.size", 1000]],
        "domain": ["size", ">", 0]
      }
    ]
  }
}
```

This chart sums document sizes and converts bytes to kilobytes directly in the operation expression.

## Backend Behavior

The default `core_model_chart` controller performs the following steps:

1. It validates the target entity.
2. It validates the grouping field.
3. It determines the range field, if applicable.
4. It validates that the range field is of type `date` or `datetime`.
5. It detects all fields referenced by dataset operations.
6. It reads the required fields from the ORM.
7. It applies the global domain and date range filtering.
8. It merges dataset-specific domains.
9. It groups matching objects by range or field.
10. It computes each dataset operation for each group.
11. It returns the result either as chart data or grid rows.

## Frontend Behavior

The frontend `LayoutChart` component performs the following steps:

1. It initializes the chart layout configuration.
2. It applies default values.
3. It parses dataset domains with the current user and environment context.
4. It converts relative date references into ISO date strings.
5. It calls the configured controller.
6. It renders the result either as:

   * a Chart.js chart when the mode is `chart`;
   * a decorated HTML table when the mode is `grid`.

For chart rendering, the frontend uses a predefined color palette.

Dataset-level color configuration is not currently used by the provided implementation. If custom colors are required, the frontend mapping logic must be extended.

## Practical Guidelines

Use `group_by: "range"` when the x-axis represents time.

Use `group_by: "field"` when the x-axis or segments represent categories, statuses, users, servers, customers, or related entities.

Use `range_field` when records must be filtered by date but grouped by another field.

Use multiple datasets when several metrics should be compared on the same chart.

Use dataset-specific domains to compare filtered subsets of the same entity.

Use `mode: "grid"` to debug or validate the computed values.

Use `bar` or `line` for most time-based metrics.

Use `pie`, `doughnut`, or `polarArea` for simple category distributions.

Be careful with `scatter` and `bubble`: these chart types are supported by Chart.js, but may require a custom controller or adapted frontend data mapping.

## Limitations and Notes

The default chart controller is designed around grouped aggregations. It does not return individual records.

The default response format is well suited to simple datasets represented as arrays of values.

Advanced Chart.js types that require object-based points, multiple axes, complex scales, or custom options may require a specific controller or frontend extension.

The `type` property affects frontend rendering only. It is passed to Chart.js but does not affect how the backend aggregates data.

The `stacked` property is applied to the `x` and `y` scales for non-segment chart types.

The current implementation does not expose full Chart.js configuration through the view schema. Only a limited subset of chart behavior is configurable through the chart view layout.
