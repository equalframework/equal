# Views

Views in eQual's `equal-ui` define how an entity is loaded, presented, filtered, edited, and acted upon in the user interface. On the client side, a view is instantiated by the `View` class with an entity, a type, a name, an initial domain, a mode, a `purpose`, a language, and an optional configuration object.

This page documents the behavior actually consumed by `equal-ui/src/View.ts`, `equal-ui/src/Model.ts`, the `Layout*` classes, and `WidgetFactory`.

---

## Lifecycle

When a view is created, `View`:

1. initializes the default configuration;
2. loads the entity translations;
3. loads the model schema;
4. loads the view schema identified by `{type}.{name}`;
5. extracts the fields required by the layout and dynamic domains;
6. prepares filters, exports, and header actions;
7. initializes the layout matching the view type;
8. loads the model through `Model.refresh()`.

The model calls the configured controller with the following parameters:

```json
{
  "get": "model_collect",
  "entity": "package\\Model",
  "fields": ["..."],
  "domain": ["..."],
  "params": {"lang": "en", "order": "id", "sort": "asc", "start": 0, "limit": 25}
}
```

The parameters returned by `getParams()` always include `lang`, `order`, `sort`, `start`, and `limit`.

---

## View Types

| Type | Client Layout | Description |
| --- | --- | --- |
| `list` | `LayoutList` | Paginated table with sorting, filters, selection, row actions, global actions, and grouping. |
| `form` | `LayoutForm` | Single-object form, in `view` or `edit` mode. |
| `search` | `LayoutSearch` | Advanced-search form used as an embedded view. |
| `chart` | `LayoutChart` | Chart.js chart or grid depending on the mode. |
| `dashboard` | `LayoutDashboard` | Grid of widgets embedding other views. |
| other | `Layout` | Base layout without specific rendering behavior. |

Widgets only support two internal layouts: `list` and `form`. Any view type other than `list` is treated as `form` for widget rendering.

---

## Modes and Purposes

### `mode`

| Type | Recognized Modes | Fallback |
| --- | --- | --- |
| `list` | `view`, `edit` | `view` |
| `form` | `view`, `edit` | `view` |
| `chart` | `chart`, `grid` | `chart` |
| other types | not normalized by `View` | received value |

### `purpose`

`purpose` describes the usage context and changes buttons, selection, and actions:

| Purpose | Main Effect |
| --- | --- |
| `view` | Normal browsing. Selection actions and exports are available according to configuration. |
| `select` | The list allows selecting one or more objects, then closes the context with `{selection, objects}`. |
| `add` | Same as `select`, but the button is labelled as an add action. |
| `create` | Edit form for a new object. Model-level readonly fields are rendered as editable. |
| `update` | Edit form for an existing object. |
| `widget` | View embedded in a relational widget or dashboard. Actions and selection are restricted. |

---

## Root Properties

| Property | Type | Default / Behavior |
| --- | --- | --- |
| `controller` | `string` | Collection controller. Client default: `model_collect`. If the view schema defines `controller`, it replaces the config value. |
| `mode` | `string` | Initial view mode, normalized according to the type. |
| `order` | `string` | Sort field. Default `id`. The view schema value is applied only if config has not already changed the default value. |
| `sort` | `string` | Sort direction. Default `asc`. |
| `start` | `number` | Pagination offset. Default `0`. |
| `limit` | `number` | Page size. Default `25`. |
| `group_by` | `array` | List grouping definitions. Default `[]`. |
| `layout` | `object` | Rendering structure, dependent on the view type. |
| `filters` | `array` or `false` | If it is an array, each filter is indexed by `id`. If the property exists but is not an array, header filters are disabled. |
| `actions` | `array` | Root-level domain actions. Their rendering depends on the layout. |
| `routes` | `array` | Contextual links, currently used as inline actions in lists. |
| `exports` | `array` | Exports added or replacing defaults indexed by the same `id`. |
| `operations` | `object` | Aggregates displayed on lists. |
| `on_empty` | `object` | For empty lists: redirects through `context` or displays `message`. |
| `on_error.missing.context` | `object` | For forms without an object: closes open contexts and opens this context. |

`name` and `description` may exist in schemas, but the `equal-ui` client mostly relies on the `{type}.{name}` identifier, translations, and the properties above.

---

## Configuration Priority

The constructor configuration and the view schema are partially merged:

- `config` initializes runtime values (`mode`, `controller`, `order`, `sort`, `start`, `limit`, `group_by`, `header`, etc.).
- `view_schema.header` is merged into `config.header`, then `config.header` keeps priority for matching keys.
- `view_schema.controller` always replaces the runtime controller.
- `view_schema.order`, `sort`, `start`, `limit`, and `group_by` only replace the runtime value if it still equals the client default.
- `view_schema.header.actions` feeds `custom_actions`, then `config.header.actions` overrides these actions.

---

## Domains and Loaded Fields

The effective domain of a view is:

```text
initial context domain + clauses/domains from applied filters
```

The result is then parsed with the current user (`user.*`). References to `env.*`, `parent.*`, and `object.*` are also used in other domain evaluations, especially visibility and relational widgets.

`View.loadViewFields()` adds to the projection fields explicitly present in the layout, but also fields referenced by:

- `visible` and `domain` in the layout;
- `routes[].context.domain`;
- `routes[].visible`;
- `actions[].visible`;
- `header.actions.*[].visible`, `domain`, `view`, `edit`, `create`, `update`.

`Model.getFieldsProjection()` then applies these rules:

- scalar field: the field is loaded directly;
- `many2one`: `field.name` is always loaded, together with subfields requested through `field.subfield` or `widget.fields`;
- `one2many` and `many2many`: not loaded in the main collection, because they are rendered by sub-views;
- `status` and `order` are added if present in the model schema.

---

## Header

`header` controls standard actions, filters, exports, pagination, and compact layout.

| Property | Type | Behavior |
| --- | --- | --- |
| `actions` | `object` or `false` | Configures standard actions. If `false`, header actions are disabled. |
| `selection` | `object` or `false` | Configures actions applicable to a selection. If `false`, selection is disabled and mode is forced to `view` for the affected lists. |
| `filters.quicksearch` | `boolean` | Shows or hides quick search on `name`. Default: enabled when filters are visible. |
| `layout` | `full` or `inline` | In `inline`, actions and controls are rendered compactly, especially for relational widgets. |
| `pagination` | `full`, `compact`, or `none` | Controls list paginator rendering. `full` displays the complete paginator, `compact` displays a lighter paginator, and `none` hides the paginator. |
| `advanced_search` | `object` or `false` | If `false`, disables advanced search. Otherwise, it is available for controllers different from `model_collect` and `core_model_collect`. |
| `advanced_search.open` | `boolean` | Opens the advanced-search panel on load. |
| `advanced_search.submit` | `auto` or `manual` | In advanced search, applies changes automatically or through a button. |
| `exports` | `false` | Disables the list export menu when `false`. |

Example:

```json
{
  "header": {
    "layout": "inline",
    "pagination": "compact",
    "filters": {
      "quicksearch": false
    },
    "advanced_search": {
      "open": true,
      "submit": "manual"
    },
    "actions": {
      "ACTION.CREATE": false
    }
  }
}
```

---

## Header Actions

A header action can be:

- `true` or `false`;
- a descriptor object;
- an array of descriptors.

An empty array is interpreted as available by `isActionEnabled()`, but some actions, notably `ACTION.SAVE`, also check that the array contains at least one variant.

### Descriptor

| Property | Type | Description |
| --- | --- | --- |
| `id` | `string` | Variant identifier (`SAVE_AND_CLOSE`, etc.) or selection-action identifier. |
| `label` | `string` | Label used for custom actions. |
| `icon` | `string` | Material icon. |
| `description` | `string` | Text used in controller-action dialogs. |
| `controller` | `string` | Controller called by a custom action or selection action. |
| `params` | `object` | Parameters injected into the controller. Values can reference `object.*`, `user.*`, and `parent.*`. |
| `confirm` | `boolean` | Shows a confirmation or forces a parameter dialog before execution. |
| `visible` | `boolean` or `Domain` | Global visibility of the action. |
| `view`, `edit`, `create`, `update` | `boolean` or `Domain` | Availability by mode/purpose depending on `isActionEnabled()` calls. |
| `domain` | `Domain` | Additional domain used by create/selection actions or to load required fields. |
| `view_id` | `string` | Target view in `type.name` format for several create/save actions. |
| `access.groups` | `string[]` | Taken into account for root-level form actions. |

Important: for `ACTION.CREATE` and save variants, the code reads `view_id`, not `view`. In `many2one` widgets, `header.view` can also be used to open the related object.

### Lists

Header actions wired for lists:

| Action | Behavior |
| --- | --- |
| `ACTION.CREATE` | Opens a form context in `mode: edit`, `purpose: create`. Can use `view_id` and merge an additional `domain`. |
| `ACTION.CREATE_INLINE` | Creates a draft object, inserts it as the first row, then switches the row to inline edit. Available in non-`view` mode, especially with `header.layout: inline`, or when explicitly configured. |
| `ACTION.SELECT` | Available for `purpose: select` or `add`; closes the context with selected objects. |

In a normal list, `ACTION.CREATE` is available by default if `purpose` is not `widget`, or if the widget list is in `edit` mode.

### Forms

Header actions wired for forms:

| Action | Behavior |
| --- | --- |
| `ACTION.EDIT` | In `view` mode, opens the same form in `mode: edit`, `purpose: update`, preserving selected sections. |
| `ACTION.SAVE` | In `edit` mode, saves through `model_update` or through the variant `controller`. |
| `ACTION.CANCEL` | Closes the context after confirmation if changes exist. |

Save variants:

| Variant | Behavior |
| --- | --- |
| `SAVE_AND_CLOSE` | Saves, marks the context as changed, closes the context, and relays `{selection, objects}`. |
| `SAVE_AND_VIEW` | Saves, then closes the parent context when possible; otherwise opens the target view in `view` mode. |
| `SAVE_AND_EDIT` | Saves, then reopens the target view in `edit` mode. |
| `SAVE_AND_CONTINUE` | Saves, replaces the domain with `id = object_id`, displays a snackbar, and reloads the view. |

The client default for `ACTION.SAVE` is `[{"id": "SAVE_AND_CLOSE"}]`.

---

## Root-Level Actions

Root-level `actions` are not the same as `header.actions`.

### Forms

In forms, root-level actions are displayed only in `view` mode:

- one visible action: direct button;
- several visible actions: `Actions` dropdown menu.

Visibility combines:

- `visible`, evaluated as a boolean, JSON string, or domain;
- `access.groups`, when defined.

### Lists

In lists:

- a root-level action without `inline: true` is displayed in the view action area, except when `purpose === "widget"`;
- an action with `inline: true` is rendered on each row;
- inline visibility is evaluated per row through `visible`.

### Execution

`decorateActionButton()` supports three families:

| Family | Trigger |
| --- | --- |
| `callback` | JavaScript function called with the current object. |
| `component` | Emits `window.dispatchEvent(new CustomEvent("App:open-component", ...))`. |
| `controller` | eQual controller call with prior announcement, optional dialog, and refresh. |

For a `controller` action:

1. `params` is initialized when absent;
2. `params.params` receives the view runtime parameters;
3. if a current object has an `id`, `id: "object.id"` is added when absent;
4. otherwise, `domain` receives the serialized current domain;
5. if the view is a widget, the parent object is loaded and made available through `parent.*`;
6. references are resolved;
7. the controller is called with `announce: true`;
8. required parameters or parameters requested by `confirm` are displayed in a dialog;
9. `performAction()` executes the action.

Responses handled by `performAction()`:

| Response | Behavior |
| --- | --- |
| non-JSON `content-type` | Download through `file-saver`. |
| HTTP `202` | "Action request sent" snackbar. |
| HTTP `205` | Marks the context as changed and closes it. |
| HTTP `302` with `Location` | Browser redirect. |
| other success statuses | Refreshes the main view of the current context. |
| error | `displayErrorFeedback()` using the controller translation. |

---

## Selection

`header.selection` configures actions available when one or more rows are selected.

| Property | Type | Behavior |
| --- | --- | --- |
| `default` | `boolean` | If absent or `true`, default actions are included. If `false`, only provided actions are used. |
| `actions` | `array` | Actions added, replaced, or hidden according to their `id`. |

Default actions:

| ID | Effect |
| --- | --- |
| `ACTION.EDIT_INLINE` | Switches selected rows to inline edit. |
| `ACTION.EDIT_BULK` | Opens bulk assignment, but the action is marked invisible by default. |
| `ACTION.EDIT` | Opens the first selected object in an edit form. |
| `ACTION.CLONE` | Clones selected objects, then reloads the view. |
| `ACTION.ARCHIVE` | Asks for confirmation, then archives selected objects. |
| `ACTION.DELETE` | Asks for confirmation, then deletes selected objects. |

A custom action with a `controller` receives at least:

```json
{
  "entity": "package\\Model",
  "ids": [1, 2, 3],
  "id": 0,
  "lang": "en"
}
```

It then follows the same announcement, dialog, and execution flow as root-level actions.

Example:

```json
{
  "header": {
    "selection": {
      "default": false,
      "actions": [
        {"id": "ACTION.CLONE"},
        {
          "id": "header.selection.actions.mark_ignored",
          "label": "Mark as ignored",
          "icon": "block",
          "controller": "lodging_sale_booking_bankstatementline_bulk-ignore",
          "confirm": true
        }
      ]
    }
  }
}
```

---

## Filters

A view filter is indexed by its `id`.

| Property | Type | Description |
| --- | --- | --- |
| `id` | `string` | Filter identifier. |
| `label` | `string` | Label. |
| `description` | `string` | Text displayed in the filter menu. |
| `clause` | `Domain` | Domain added to applied filters. |
| `domain` | `Domain` | Alternative to `clause`, also merged into the current domain. |

Applied filters are cumulative. The menu also allows adding a custom filter through `decorateCustomFilterDialog()`.

Quick search dynamically creates a `filter_search_on_name` filter:

- `["name", "ilike", "%value%"]` if `name.result_type === "string"`;
- otherwise `["name", "=", "value"]`.

---

## Exports

Defaults:

- list: `export.pdf` through `model_export-pdf` and `export.xls` through `model_export-xls`;
- chart: `export.xls` through `model_export-chart-xls`.

Exports defined in the schema are added by `id` and can replace a default export.

| Property | Type | Description |
| --- | --- | --- |
| `id` | `string` | Identifier. |
| `label` | `string` | Label translated through `view.{view_id}.exports`. |
| `icon` | `string` | Optional icon. |
| `controller` | `string` | Export controller. |
| `view` | `string` | View passed to the controller; default: current view. |

The export menu opens a backend URL with `get`, `entity`, `view_id`, `domain`, `lang`, `controller`, `nolimit: true`, and `params`.

---

## List Layout

A list expects a flat layout:

```json
{
  "layout": {
    "items": [
      {"type": "field", "value": "name", "width": 40, "sortable": true},
      {"type": "field", "value": "status", "width": 20}
    ],
    "interactions": {
      "click": true
    }
  }
}
```

### List Items

| Property | Type | Description |
| --- | --- | --- |
| `value` | `string` | Model field. Items without a valid field are ignored. |
| `label` | `string` | Fallback label, overridden by the model and then translations. |
| `width` | `number` | Width as a percentage. Minimum forced to `10`, then the total is normalized to `100`. |
| `visible` | `boolean` or `Domain` | If `false`, the column is ignored at layout time; if a domain, visibility is evaluated per row. |
| `sortable` | `boolean` | Enables sorting by clicking the header. |
| `align` | `left`, `center`, `right` | Alignment. Default: `right` for `integer`, `float`, `time` except `id`, otherwise `left`. |
| `widget` | `object` | Widget configuration override. |
| `styles` | `array` | Conditional styles evaluated per row for scalar fields. |

Interactions:

| Property | Effect |
| --- | --- |
| `layout.interactions.click` | Configure how clicking a row reacts (opening object in a ne context or not). |
| `layout.interactions.autofocus` | Configure how selectable fields react. |

By default, clicking a row opens `form.{name}` on the same object. If the list is a widget, closing that context triggers a refresh of the parent view.

---

## Form Layout

A form expects a nested structure:

```json
{
  "layout": {
    "groups": [
      {
        "id": "main",
        "label": "Main",
        "sections": [
          {
            "id": "general",
            "label": "General",
            "rows": [
              {
                "columns": [
                  {
                    "width": 50,
                    "items": [
                      {"type": "field", "value": "name", "width": 100},
                      {"type": "label", "id": "hint", "value": "Static text"}
                    ]
                  }
                ]
              }
            ]
          }
        ]
      }
    ],
    "interactions": {
      "autofocus": true
    }
  }
}
```

### Supported Nodes

| Level | Main Properties |
| --- | --- |
| `groups[]` | `id`, `label`, `visible`, `sections[]` |
| `sections[]` | `id`, `label`, `visible`, `rows[]` |
| `rows[]` | `visible`, `columns[]` |
| `columns[]` | `width`, `align`, `visible`, `items[]` |
| `items[]` | `type`, `value`, `id`, `label`, `width`, `align`, `visible`, `readonly`, `required`, `widget` |

Widths are converted to Material Design columns on a 12-column grid:

```text
span = round(width / 100 * 12)
```

Static labels (`type: "label"`) are rendered with the `label` widget and translated through `view.{view_id}.layout.{id}`.

### Autofocus

By default, `LayoutForm` focuses the first direct input of the first widget after rendering. To disable it:

```json
{
  "layout": {
    "interactions": {
      "autofocus": false
    }
  }
}
```

### Onchange

When a widget emits `_updatedWidget`, the form:

1. updates the local object;
2. calls `model_onchange` when the value is small enough or when it is a file with metadata;
3. applies returned value changes;
4. updates returned domains, selections, or field definitions;
5. triggers `onchangeViewModel()`.

`required` fields are checked by the layout before saving. For a `many2one`, presence is checked through `object_id`.

---

## Search Layout

`LayoutSearch` reuses the form structure: `groups -> sections -> rows -> columns -> items`. It is used for advanced search.

Main differences:

- relational widgets do not show create/open actions;
- `advanced_search.submit: "auto"` applies changes immediately;
- `advanced_search.submit: "manual"` accumulates changes and shows a search button;
- changes feed the parent view parameters, then trigger its refresh.

---

## Dashboard Layout

The dashboard also uses `groups -> sections -> rows -> columns -> items`, but each expected item embeds another view.

| Property | Type | Description |
| --- | --- | --- |
| `entity` | `string` | Entity of the embedded view. |
| `view` | `string` | View in `type.name` format; default `list.default`. |
| `domain` | `Domain` | Domain of the embedded view. |
| `width` | `number` | Cell width on the 12-column grid. |
| `height` | `string` | Cell CSS height. |

Rows can also define `height` as a percentage of the context's available height.

---

## Chart Layout

`LayoutChart` reads its configuration from `layout`.

| Property | Default | Description |
| --- | --- | --- |
| `type` | `bar` | Chart.js type (`bar`, `line`, `pie`, etc.). |
| `stacked` | `false` | Dataset stacking for some chart types. |
| `group_by` | `range` | Grouping sent to the chart controller. |
| `field` | `created` | Reference temporal field. |
| `range_field` | absent | Optional range field. |
| `range_interval` | `month` | Grouping interval. |
| `range_from` | `date.this.year.first` | Relative start date. |
| `range_to` | `date.this.year.last` | Relative end date. |
| `entity` | optional | Target entity sent to the controller. |
| `datasets` | required | Chart datasets. Each dataset defaults to `label: "label"` and `operation: ["COUNT", "object.id"]`. |

The called controller is `core_model_chart`, unless overridden by `view_schema.controller`.

In `grid` mode, the result is rendered as a table. Otherwise, it is rendered in a Chart.js canvas.

---

## Widgets and Field Configuration

`WidgetFactory.getWidgetConfig()` merges the model schema and the view item.

| Source | Effect |
| --- | --- |
| model `label`, `description`, `help`, `usage`, `type`, `readonly`, `required`, `selection`, `foreign_object`, `foreign_field`, `domain`, `visible` | Defines the widget base. |
| view item `label`, `description`, `help`, `readonly`, `required`, `align`, `sortable`, `visible`, `widget` | Overrides or completes the base. |
| `item.widget` | Final widget configuration override. |

Important rules:

- if the model defines `selection`, the widget becomes `select`;
- `usage` can convert the final type (`text/html` -> `text`, `uri/url` -> `link`, `image/png` -> `file`, etc.);
- `purpose: "create"` forces `readonly: false`, even if the model is readonly;
- `visible` is serialized as JSON before evaluation;
- `layout` is `list` only for views of type `list`; otherwise it is `form`.

Main widget types:

| Final Type | Widget |
| --- | --- |
| `boolean` | `WidgetBoolean` |
| `date` | `WidgetDate` |
| `time` | `WidgetTime` |
| `datetime` | `WidgetDateTime` |
| `integer` | `WidgetInteger` |
| `float` | `WidgetFloat` |
| `string` | `WidgetString`, except color usages rendered with `WidgetSelect` |
| `text` | `WidgetText` |
| `link` | `WidgetLink` |
| `binary` / `file` | `WidgetFile`, `WidgetImage`, or `WidgetSignature` depending on `usage` |
| `pdf` | `WidgetPdf` |
| `upload` | `WidgetUpload` |
| `many2one` | `WidgetMany2One` |
| `one2many` | `WidgetOne2Many` |
| `many2many` | `WidgetMany2Many` |
| `label` | `WidgetLabel` |

---

## Relational Fields

### `many2one`

A `many2one` is displayed from `{id, name}`. The model always loads `field.name`.

Notable options:

| Option | Behavior |
| --- | --- |
| `widget.view` | Default view used for relational sub-contexts. |
| `widget.header.view` | View used to open the related object. |
| `widget.header.actions.ACTION.CREATE` | Can contain `domain` and `view_id` for creation. |
| `widget.header.actions.ACTION.OPEN` | Enables/disables the open button. |
| `widget.header.actions.ACTION.SELECT` | Enables/disables advanced selection. |
| `widget.header: false` or `widget.header.actions: false` | Disables create/open/select. |
| `component` | Delegates creation to an application component. |

In edit mode, the widget allows:

- resetting the relation;
- opening the related object;
- creating through a form or component;
- instant creation through `ApiService.create(foreign_object, {name})`;
- advanced search through a `purpose: select` list.

### `one2many` and `many2many`

These fields are rendered as embedded views. The main collection does not load relations.

The sub-view domain is built from:

- the model `domain`;
- the widget `domain`;
- join condition on `foreign_field` and `object_id`;
- `ids_to_add` and `ids_to_del` to reflect local changes.

For `many2many`, selection is available only in `edit` mode. Local removals are represented by negative ids.

---

## Conditional Visibility

`visible` properties can be:

- `true` or `false`;
- the string `"true"` or `"false"`;
- a JSON string representing a domain;
- a domain array.

Evaluation uses:

```text
object, user, parent, env
```

Visibility domains are used for:

- actions;
- routes;
- groups, sections, rows, columns, and items;
- widgets;
- action-dialog parameters.

---

## Grouping

`group_by` is used by `LayoutList`.

Accepted forms:

```json
{
  "group_by": ["date"]
}
```

```json
{
  "group_by": [
    {
      "field": "time_slot_id",
      "operation": ["SUM", "object.qty"],
      "operations": {
        "qty": {"operation": "SUM", "usage": "number/integer"}
      },
      "order": "name",
      "usage": "date/month",
      "open": true,
      "colspan": 2
    }
  ]
}
```

| Property | Description |
| --- | --- |
| `field` | Grouping field. |
| `operation` | Aggregate displayed in the group label. |
| `operations` | Per-column aggregates, displayed in the remaining group cells. |
| `order` | For object keys, subfield used as sort key. |
| `usage` | Usage applied to the group label, especially for dates. |
| `open` | When defined on the first grouping, forces the open/closed state after refresh. |
| `colspan` | Number of columns covered by the group label cell. |

Dates are normalized as `YYYY-MM-DD` keys and formatted through `getMomentFormatFromUsage()`. Empty keys become `no_value` with label `empty`.

---

## Operations

Operations are used:

- in `group_by[].operation`;
- in `group_by[].operations`;
- at root-level `operations` to display aggregate rows under the table.

Supported operators:

| Operator | Description |
| --- | --- |
| `SUM` | Sum of non-null values. |
| `COUNT` | Count of non-null values, or object count if no field is provided. |
| `MIN` | Minimum. |
| `MAX` | Maximum. |
| `AVG` | Incremental average. |
| `DIFF` | Difference between two nested operations. |

An operation can be a string or an array:

```json
{"operation": "COUNT"}
```

```json
{"operation": ["SUM", "object.amount"]}
```

```json
{"operation": ["DIFF", ["SUM", "object.total"], ["SUM", "object.paid"]]}
```

Root-level example:

```json
{
  "operations": {
    "total": {
      "amount": {
        "operation": ["SUM", "object.amount"],
        "usage": "amount/money:2",
        "prefix": "EUR "
      }
    }
  }
}
```

The final value is formatted by `Widget.toString()` according to the model type or provided `usage`.

---

## Routes

Routes are handled by `LayoutList` as inline buttons when `inline: true`.

| Property | Type | Description |
| --- | --- | --- |
| `inline` | `boolean` | Only inline routes are rendered in rows. |
| `visible` | `boolean` or `Domain` | Evaluated per row. |
| `context` | `object` | Context to open. |
| `context.domain` | `Domain` | `object.field` references are replaced by values from the current object. |
| `context.display_mode` | `popup` | Opens the context in a popup through the frame listener. |

---

## Rich Text

`WidgetText` uses Quill in form edit mode.

Rules by `usage`:

| Usage | Edit Mode | View Mode |
| --- | --- | --- |
| `text/html` | HTML normalized for Quill, paragraphs ensured. | HTML rendering. |
| `text/plain` | Quill editing, but output is plain text through `getText().trim()`. | Line breaks converted to `<br />`. |
| `text/json` | Text editing. | Escaped rendering in `<pre><code class="language-json">`. |

The widget debounces Quill changes for about 1 second and emits `_updatedWidget` without immediate refresh (`refresh = false`).

---

## Examples

### List with Inline Action, Grouping, and Aggregates

```json
{
  "controller": "model_collect",
  "order": "date",
  "sort": "desc",
  "layout": {
    "items": [
      {"type": "field", "value": "date", "width": 20, "sortable": true},
      {"type": "field", "value": "customer_id", "width": 30},
      {"type": "field", "value": "amount", "width": 20, "sortable": true}
    ]
  },
  "group_by": [
    {
      "field": "date",
      "usage": "date/month",
      "open": true,
      "operations": {
        "amount": {
          "operation": ["SUM", "object.amount"],
          "usage": "amount/money:2"
        }
      }
    }
  ],
  "actions": [
    {
      "id": "action.validate",
      "label": "Validate",
      "icon": "done",
      "inline": true,
      "controller": "sale_order_validate",
      "visible": ["status", "=", "draft"],
      "confirm": true
    }
  ]
}
```

### Form with Sections and Custom Save

```json
{
  "mode": "view",
  "header": {
    "actions": {
      "ACTION.SAVE": [
        {"id": "SAVE_AND_CLOSE"},
        {"id": "SAVE_AND_CONTINUE", "controller": "sale_order_update-custom"}
      ]
    }
  },
  "layout": {
    "groups": [
      {
        "id": "main",
        "sections": [
          {
            "id": "general",
            "label": "General",
            "rows": [
              {
                "columns": [
                  {
                    "width": 50,
                    "items": [
                      {"type": "field", "value": "name"},
                      {"type": "field", "value": "customer_id"}
                    ]
                  },
                  {
                    "width": 50,
                    "items": [
                      {"type": "field", "value": "description", "widget": {"height": 240}}
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

### `many2one` Field with Controlled Creation

```json
{
  "type": "field",
  "value": "customer_id",
  "widget": {
    "view": "list.default",
    "header": {
      "view": "form.default",
      "actions": {
        "ACTION.CREATE": [
          {
            "view_id": "form.create",
            "domain": ["category", "=", "customer"]
          }
        ],
        "ACTION.OPEN": true,
        "ACTION.SELECT": true
      }
    }
  }
}
```
