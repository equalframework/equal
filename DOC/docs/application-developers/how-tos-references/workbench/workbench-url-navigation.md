# Query Parameter Navigation Reference

Workbench routing follows a hybrid pattern. Some URLs load dedicated feature screens, while others resolve to a shared multi-purpose entry screen. This is intentional: the Workbench home screen is not just a landing page, but a generic inspector driven by the current URL. It interprets the route, restores the selected package, model, view, menu, controller, or route, and then renders the corresponding information panel.

As a result, several routes point to the same `AppComponent`. That component acts as a universal URL-driven inspector, whereas more specialized sub-routes load dedicated editing modules for tasks such as fields, translations, workflow, policies, actions, roles, or view editing.

## Workbench Route Recap

| Full URL | Target |
|---|---|
| `/uml` | `UmlErdComponent` |
| `/pipelines` | `PipelineComponent` |
| `/routes` | `RoutesComponent` |
| `/package/:package_name` | `AppComponent` |
| `/package/:package_name/init-data/:type` | `InitDataComponent` |
| `/package/:package_name/model/:class_name/fields` | `PackageModelFieldsComponent` |
| `/package/:package_name/model/:class_name/translations` | `ModelTradEditorComponent` |
| `/package/:package_name/model/:class_name/workflow` | `PackageModelWorkflowComponent` |
| `/package/:package_name/model/:class_name/policies` | `PackageModelPoliciesComponent` |
| `/package/:package_name/model/:class_name/actions` | `PackageModelActionsComponent` |
| `/package/:package_name/model/:class_name/roles` | `PackageModelRolesComponent` |
| `/package/:package_name/model/:class_name` | `AppComponent` |
| `/package/:package_name/menu/:menu_name/translations` | `MenuTradEditorComponent` |
| `/package/:package_name/menu/:menu_name/edit` | `PackageMenuComponent` |
| `/package/:package_name/menu/:menu_name` | `AppComponent` |
| `/package/:package_name/controller/:controller_type/:controller_name/edit` | `PackageControllerComponent` |
| `/package/:package_name/controller/:controller_type/:controller_name` | `AppComponent` |
| `/package/:package_name/route/:route_name` | `AppComponent` |
| `/package/:package_name/routes` | `AppComponent` |
| `/package/:package_name/package/:package_name` | `PackageComponent` |
| `/package/:package_name/view/:entity_view/edit` | `PackageViewComponent` |
| `/package/:package_name/view/:entity_view` | `AppComponent` |

---

This document lists the available query parameters and link patterns used for deep navigation in editors. This is intended as a reference for developers to understand and utilize the navigation system effectively. For specifics about implementation and usage, refer to the `QueryParamNavigatorService` code and related editor components.

---

## General Rules

- Main params used by navigation:
    - `tab`: activates a main tab
    - `field`: target field path to scroll/focus

These are the most common, but other params may be used for specific sections, such as:
    - `view`: activates a view
    - `view_tab`: activates a sub-tab inside a view (for example `layout`, `actions`, `routes`)
    - `lang`: selects a translation language (for example `en`, `es`, `fr`)
- Effective field key is built as:
    - `tab-view-view_tab-field`
    - Prefixes are only added when present.

Example:

```text
?tab=advanced&view=form.default&view_tab=layout&field=customer-label
```

When focusing a field, the system will attempt to click it open if it's inside a collapsible section (like an accordion or a dropdown). However, this may not work in all cases, and some manual interaction might be required to open the section before the field can be focused. An example of this are checkboxes, the service will always verify if the target checkbox is already checked before attempting to click it, but if it's not checked, it will try to click the checkbox to open the section, which may not be the desired behavior in some cases. This is an area that may require further refinement in the future.

---

## Models

### Fields

- Base field selector:
    - `field=fieldname`

- Field targets in basic tab:
    - `fieldname-name`
    - `fieldname-type`
    - `fieldname-function`
    - `fieldname-result_type`
    - `fieldname-rel_table`
    - `fieldname-rel_local_key`
    - `fieldname-rel_foreign_key`
    - `fieldname-description`
    - `fieldname-readonly`
    - `fieldname-required`
    - `fieldname-multilang`
    - `fieldname-unique`
    - `fieldname-store`
    - `fieldname-instant`
    - `fieldname-has_default`
    - `fieldname-has_selection`
    - `fieldname-default`
    - `fieldname-selection_add`
    - `fieldname-selection_<index>`

Model tab values:

- `tab=basic`
- `tab=advanced`

Examples:

```text
?field=customer-name
?tab=advanced
?tab=basic&field=customer-selection_0
```

### Translations

- Params:
    - `lang=langname` (for example `en`, `es`, `fr`)
    - `tab=model` or `tab=view` or `tab=error`
    - `view=viewname` (for example `form.default` or `list.dashboard`)
    - `view_tab=layout` or `view_tab=actions` or `view_tab=routes`
    - `field=fieldname-field` or `field=fieldname-label` or `field=fieldname-description` or `field=fieldname-help`

Examples:

```text
?lang=en&tab=model&field=customer-label
?lang=fr&tab=view&view=form.default&view_tab=layout&field=customer-help
?lang=es&tab=error&field=customer-description
```

### Generic/High-Level Sections

Since current implementation of the following section is rudimentary, the navigation is yet to be defined:

- `workflow`
- `roles`
- `policies`
- `actions`

---

## Controller

Main tabs:

- `tab=response`
- `tab=parameters`

### Response

Field targets:

- `header-content_type`
- `header-charset`
- `header-accept_origin`
- `schema-return_type`
- `schema-qty`

Examples:

```text
?tab=response&field=header-content_type
?tab=response&field=schema-return_type
```

### Parameters

Base selector:

- `field=fieldname`

Common field targets:

- `fieldname-type`
- `fieldname-foreign_object`
- `fieldname-description`
- `fieldname-is_required`
- `fieldname-has_default`
- `fieldname-has_visibility`
- `fieldname-has_selection`
- `fieldname-has_domain`

Examples:

```text
?field=invoice_id-type
?tab=parameters&field=invoice_id-has_selection
```

---

## Menu

> Note: In menus to access an item, use its id as field selector, and not the name as in models. This behavior should certainly be unified in the future.

### Edit

Base selector:

- `field=fieldid`

Common field targets:

- `fieldid-id`
- `fieldid-label`
- `fieldid-icon`
- `fieldid-type`
- `fieldid-description`
- `fieldid-route`
- `fieldid-purpose`
- `fieldid-display_mode`
- `fieldid-sort`

Examples:

```text
?field=app.settings-label
?field=blog.menus.posts-route
```

### Translations

Base selector:

- `lang=langcode` (for example `en`, `es`, `fr`)
- `field=fieldid`

Common field targets:

- `fieldid-label`

Examples:

```text
?lang=en&field=parent.child.1-label
?lang=fr&field=child.2-label
```

## View

Main tabs:

- `tab=layout`
- `tab=header`
- `tab=actions`
- `tab=routes`
- `tab=advanced`

### Layout

Base selector:

- `field=fieldname`

Common field targets:

- `fieldname-id`
- `fieldname-type`
- `fieldname-value`
- `fieldname-width`
- `fieldname-label`
- `fieldname-is_readonly`
- `fieldname-has_visibility`
- `fieldname-has_widget`

Examples:

```text
?tab=layout&field=customer-id
?tab=layout&field=customer-has_widget
```

### Header

Reserved for header-specific navigation.

Example:

```text
?tab=header
```

---

### Actions

Base selector:

- `field=fieldname-id`

Common field targets:

- `fieldname-label`
- `fieldname-icon`
- `fieldname-controller`
- `fieldname-description`
- `fieldname-has_confirm`
- `fieldname-has_access`
- `fieldname-has_domain`

Examples:

```text
?tab=actions&field=validate_order-id
?tab=actions&field=validate_order-has_confirm
```

---

### Routes

Base selector:

- `field=fieldname-id`

Common field targets:

- `fieldname-route`
- `fieldname-label`
- `fieldname-description`
- `fieldname-icon`
- `fieldname-has_context`
- `fieldname-has_visible`

Examples:

```text
?tab=routes&field=open_invoice-route
?tab=routes&field=open_invoice-has_visible
```

## Full URL Examples

Use these by appending to the current editor route.

In model translations: 
```text
http://equal.local/#/package/core/model/alert%5CMessage/translations?tab=view&view=form.default&view_tab=actions&field=action.retry-label
http://equal.local/#/package/core/model/alert%5CMessage/translations?tab=error&field=controller
http://equal.local/#/package/core/model/alert%5CMessage/translations?tab=model&field=name
```

In menu translations:
```text
?tab=translations&lang=es&field=app.settings-label
```
