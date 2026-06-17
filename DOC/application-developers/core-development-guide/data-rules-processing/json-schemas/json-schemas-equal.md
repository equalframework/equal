# eQual JSON Schema Definitions

This directory contains JSON Schema definitions (Draft 2020-12) for major eQual framework components. These schemas are used by the `json-validate` data action, usually through dedicated consistency controllers that load the target JSON internally.

---

## Available Schemas

### 1. **Package Manifest** (`manifest.json`)
- **Schema ID**: `urn:equal:json-schema:core:package.manifest`
- **Usage**: Validate `packages/{package}/manifest.json` files
- **Required fields**: `name`, `version`
- **Common fields**: `description`, `license`, `authors`, `depends_on`, `requires`, `requires_php8`, `apps`, `roles`, `tags`

---

### 2. **Model Class** (`model.json`)
- **Schema ID**: `urn:equal:json-schema:core:model.class`
- **Usage**: Validate model class definitions (JSON representation of *.class.php)
- **Required fields**: `name`, `fields`
- **Common fields**: `description`, `parent`, `table`, `access`, `workflow`
- **Notes**: `fields` contains canonical model fields and allows additional field definitions.

---

### 3. **Controller Action** (`controller.json`)
- **Schema ID**: `urn:equal:json-schema:core:controller.action`
- **Usage**: Validate action controller definitions (JSON representation of data action PHP)
- **Required fields**: `type`, `name`, `package_name`
- **Common fields**: `description`, `deprecated`, `help`, `params`, `response`, `access`, `providers`, `constants`, `examples`

---

### 4. **API Route** (`route.json`)
- **Schema ID**: `urn:equal:json-schema:core:api.route`
- **Usage**: Validate API route definitions in `packages/{package}/init/routes/*.json` and generated `config/routing/*.json`
- **Structure**: Object keyed by URI path (e.g. `"/user/:ids"`) mapping to either:
  - HTTP method descriptors (`GET`, `POST`, `PUT`, `DELETE`, `PATCH`, etc.)
  - a shorthand operation string (GET implied)
- **Method object fields**: `operation` (required), optional `description`, `params`
- **Compatibility**: Legacy route arrays are also supported

---

### 5. **Form View** (`view.form.default.json`)
- **Schema ID**: `urn:equal:json-schema:core:view.form`
- **Usage**: Validate form views (`Model.form.*.json`)
- **Required fields**: `name`, `layout`
- **Layout requirements**: `layout.groups` is required
- **Common fields**: `description`, `icon`, `context`, `routes`, `actions`, `menus`

---

### 6. **List View** (`view.list.default.json`)
- **Schema ID**: `urn:equal:json-schema:core:view.list`
- **Usage**: Validate list/table views (`Model.list.*.json`)
- **Required fields**: `name`, `layout`
- **Layout requirements**: `layout.items` is required
- **Common fields**: `order`, `sort`, `limit`, `filters`, `header`, `actions`, `bulk_actions`

---

### 7. **Dashboard View** (`view.dashboard.default.json`)
- **Schema ID**: `urn:equal:json-schema:core:view.dashboard`
- **Usage**: Validate dashboard views (`*.dashboard.*.json`) - composite layouts
- **Required fields**: `name`, `layout`
- **Layout requirements**: `layout.items` is required
- **Item types**: `widget`, `view`, `card`, `group`
- **Common fields**: `refresh_interval`, `actions`

---

### 8. **Search View** (`view.search.default.json`)
- **Schema ID**: `urn:equal:json-schema:core:view.search`
- **Usage**: Validate search/filter views (`*.search.*.json`)
- **Required fields**: `name`, `layout`
- **Layout options**: `layout.groups` (grouped layout) and/or `layout.sections` (flat layout)
- **Common fields**: `entity`, `order`, `sort`, `start`, `limit`, `domain`, `default_filters`

---

### 9. **Menu** (`menu.json`)
- **Schema ID**: `urn:equal:json-schema:core:menu`
- **Usage**: Validate menu definitions (`menu.{app}.{position}.json`)
- **Required fields**: `layout`
- **Top-level fields**: `name`, `access`, `search`, `layout`
- **Menu item required field**: `id`
- **Menu item types**: `entry`, `parent`, `link`, `submenu`, `divider`, `header`
- **Nested item fields**: `items` and `children` are both supported
- **Common item fields**: `label`, `icon`, `description`, `action`, `route`, `visible`, `roles`, `params`, `context`, `badge`

---

### 10. **Model Translations** (`model-translations.json`)
- **Schema ID**: `urn:equal:json-schema:core:model.translations`
- **Usage**: Validate model/interface translations (`packages/{package}/i18n/{lang}/{Model}.json`)
- **Supported shapes**:
  - Single translation file object (`name`, `plural`, `description`, `model`, `view`, `error`)
  - Optional language-map wrapper (`{ "fr": { ... }, "en": { ... } }`)

---

### 11. **Menu Translations** (`menu-translations.json`)
- **Schema ID**: `urn:equal:json-schema:core:menu.translations`
- **Usage**: Validate menu translations (`packages/{package}/i18n/{lang}/menu.*.json`)
- **Supported shapes**:
  - Single translation file object (`name`, `description`, `view`)
  - Optional language-map wrapper (`{ "fr": { ... }, "en": { ... } }`)

---

## Usage Examples

Prefer the consistency controllers below instead of passing raw JSON in a CLI or URL parameter. They avoid command-line escaping issues by loading the JSON file or view from PHP before calling `json-validate`.

### Validate a Form View

Prefer the consistency controller for model views. It loads the view definition and validates it internally.

```
GET http://equal.local/?do=core_test_view-consistency
  &entity='core\User'
  &view_id='form.default'
```

CLI equivalent:

```
php run.php --do=core_test_view-consistency --entity=core\User --view_id=form.default
```

### Validate a Package Manifest

```
GET http://equal.local/?do=core_test_manifest-consistency
  &package=core
```

### Validate an API Route

```
GET http://equal.local/?do=core_test_route-consistency
  &package=core
  &file=99-default.json
```

CLI equivalent:

```
php run.php --do=core_test_route-consistency --package=core --file=99-default.json
```

### Validate a Model Translation

```
php run.php --do=core_test_translation-consistency --entity=core\User --lang=en
```

### Validate a Menu Definition

```
php run.php --do=core_test_menu-consistency --package=core --menu_id=settings.left
```

### Validate a Dashboard View

```
php run.php --do=core_test_dashboard-consistency --entity=core\alert\Message --view_id=dashboard.default
```

---

## Strict vs Non-Strict Validation

- **Strict** (default): Validates structure, types, and mandatory fields
- **Non-Strict** (`?strict=false`): Makes all fields optional and nullable

Append `&strict=false` to queries for lenient validation during development.

---

## Integration Points

These schemas are retrieved by the `json-schema` data action:
```
GET ?get=core_json-schema&id={schema_id}
```

They can be validated directly by the `json-validate` data action when no dedicated consistency controller exists:
```
GET ?get=core_json-validate&schema_id={schema_id}&json={json}&strict={boolean}
```

Avoid passing large JSON strings directly in shell commands or URLs. Prefer a consistency controller, or pass JSON through a UTF-8 variable when direct validation is unavoidable.

---

## Schema Extension

To create additional schemas in other packages:

1. Create a `schemas` directory in your package: `packages/{package}/schemas/`
2. Create schema files following a clear naming convention used by your package.
3. Use schema ID pattern: `urn:equal:json-schema:{package}:{schema_name}`

Example for blog package:
- Schema ID: `urn:equal:json-schema:blog:Post.form.default`
- File location: `packages/blog/schemas/Post.form.default.json`
