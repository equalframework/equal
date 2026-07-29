# eQual Consistency and Schema Validation for Agent-Created Content

When agents create or modify content, they must validate the structure using available JSON schemas.

Prefer dedicated consistency controllers whenever one exists. These controllers load the target file or view internally and call `json-validate` from PHP, which avoids fragile shell escaping for full JSON payloads.

Do not manually extract, transform, or pass full JSON payloads for views, model translations, menus, routes, or manifests when a dedicated `core_test_*_consistency` controller exists.

| Content type | Preferred controller |
| --- | --- |
| Model views (`form`, `list`, `chart`, `search`, `dashboard`) | `php run.php --do=core_test_view-consistency --entity={EntityName} --view_id={type}.{name}` |
| Dashboard views only | `php run.php --do=core_test_dashboard-consistency --entity={EntityName} --view_id=dashboard.{name}` |
| Model translations | `php run.php --do=core_test_translation-consistency --entity={EntityName} --lang={lang}` |
| Menu definitions | `php run.php --do=core_test_menu-consistency --package={package} --menu_id={app}.{position}` |
| Package route files | `php run.php --do=core_test_route-consistency --package={package} --file={priority-name.json}` |
| Package manifest | `php run.php --do=core_test_manifest-consistency --package={package}` |

Use `core_json-validate` directly only when no dedicated consistency controller covers the content type, or when validating an in-memory JSON representation such as controller `announcement` metadata.

## Controller CLI Name Resolution

For action handlers and data providers, the CLI controller name is derived from the PHP file path.

| File path | Controller type | CLI call |
| --- | --- | --- |
| `packages/{package}/actions/{name}.php` | action handler | `./equal.run --do={package}_{name}` |
| `packages/{package}/actions/{dir}/{name}.php` | action handler | `./equal.run --do={package}_{dir}_{name}` |
| `packages/{package}/data/{name}.php` | data provider | `./equal.run --get={package}_{name}` |
| `packages/{package}/data/{dir}/{name}.php` | data provider | `./equal.run --get={package}_{dir}_{name}` |

For nested paths, replace each `/` path separator below `actions/` or `data/` with `_`. Keep the PHP filename as-is without the `.php` extension, including hyphens when the file name contains them.

Use `--announce=true` to return the `eQual::announce()` metadata as JSON and stop before the controller business logic runs. This is the preferred way to validate controller params, response, access, providers, and constants.

## Schema Reference by Component Type

### Entities (Model Classes)
- **Schema ID**: `urn:equal:json-schema:core:model.class`
- **Usage**: Validate the JSON representation of ORM model class definitions
- **Required fields**: `name`, `fields`
- **Export the model schema** with controller `core_model_schema`: `./equal.run --get=core_model_schema --entity={EntityName}`
- **Do not parse the `.class.php` file manually**; let eQual build the model representation through `core_model_schema`.
- **Validate** through `core_json-validate` with the JSON representation and schema ID
- **Validation example**:
  ```
  ./equal.run --get=core_model_schema --entity={EntityName}
  # then validate the returned JSON representation with core_json-validate.
  ```

### Views - Form
- **Schema ID**: `urn:equal:json-schema:core:view.form`
- **Usage**: Validate form view files (Model.form.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.form.*.json`
- **Validation example**:
  ```
  php run.php --do=core_test_view-consistency --entity={EntityName} --view_id=form.{name}
  ```

### Views - List
- **Schema ID**: `urn:equal:json-schema:core:view.list`
- **Usage**: Validate list/table view files (Model.list.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.list.*.json`
- **Validation example**:
  ```
  php run.php --do=core_test_view-consistency --entity={EntityName} --view_id=list.{name}
  ```

### Views - Chart
- **Schema ID**: `urn:equal:json-schema:core:view.chart`
- **Usage**: Validate chart view files (Model.chart.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.chart.*.json`
- **Validation example**:
  ```
  php run.php --do=core_test_view-consistency --entity={EntityName} --view_id=chart.{name}
  ```

### Views - Dashboard
- **Schema ID**: `urn:equal:json-schema:core:view.dashboard`
- **Usage**: Validate dashboard view files (*.dashboard.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.dashboard.*.json`
- **Validation example**:
  ```
  php run.php --do=core_test_dashboard-consistency --entity={EntityName} --view_id=dashboard.{name}
  ```

### Views - Search
- **Schema ID**: `urn:equal:json-schema:core:view.search`
- **Usage**: Validate search/filter view files (*.search.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.search.*.json`
- **Validation example**:
  ```
  php run.php --do=core_test_view-consistency --entity={EntityName} --view_id=search.{name}
  ```

### Menus
- **Schema ID**: `urn:equal:json-schema:core:menu`
- **Usage**: Validate menu definition files
- **Required fields**: `layout`
- **File pattern**: `packages/{package}/views/menu.{app}.{position}.json`
- **Validation example**:
  ```
  php run.php --do=core_test_menu-consistency --package={package} --menu_id={app}.{position}
  ```

### Action Handlers
- **Schema ID**: `urn:equal:json-schema:core:controller.action`
- **Usage**: Validate action handler definitions (JSON representation of PHP action files)
- **Required fields**: `type`, `name`, `package_name`
- **File pattern**: `packages/{package}/actions/{path}/{action}.php`
- **Get JSON representation**: Use `./equal.run --do={package}_{path}_{action} --announce=true` to have eQual return the action metadata as JSON.
- **Validate** through `core_json-validate` with the returned metadata and schema ID.
- **Exception note**: This direct `core_json-validate` flow is allowed because no dedicated consistency controller currently covers action announcement metadata. Do not parse the PHP file manually.
- **Validation example**:
  ```
  ./equal.run --do={package}_{path}_{action} --announce=true
  # then validate the returned announcement JSON with core_json-validate.
  ```

### Data Providers
- **Schema ID**: `urn:equal:json-schema:core:controller.action`
- **Usage**: Same as action handlers; data providers are validated as controller actions
- **Required fields**: `type`, `name`, `package_name`
- **File pattern**: `packages/{package}/data/{path}/{provider}.php`
- **Get JSON representation**: Use `./equal.run --get={package}_{path}_{provider} --announce=true` to have eQual return the provider metadata as JSON.
- **Validate** through `core_json-validate` with the returned metadata and schema ID.
- **Exception note**: This direct `core_json-validate` flow is allowed because no dedicated consistency controller currently covers provider announcement metadata. Do not parse the PHP file manually.
- **Validation example**:
  ```
  ./equal.run --get={package}_{path}_{provider} --announce=true
  # then validate the returned announcement JSON with core_json-validate.
  ```

### Model Translations
- **Schema ID**: `urn:equal:json-schema:core:model.translations`
- **Usage**: Validate model/entity translation files
- **File pattern**: `packages/{package}/i18n/{lang}/{EntityName}.json`
- **Contains**: Field labels, descriptions, help text, view translations, and error messages
- **Validation example**:
  ```
  php run.php --do=core_test_translation-consistency --entity={EntityName} --lang={lang}
  ```

### Menu Translations
- **Schema ID**: `urn:equal:json-schema:core:menu.translations`
- **Usage**: Validate menu translation files
- **File pattern**: `packages/{package}/i18n/{lang}/menu.{type}.{position}.json`
- **Contains**: Menu item labels, descriptions, and layout translations
- **Validation example**:
  ```
  $json = Get-Content -Raw -Encoding UTF8 packages/{package}/i18n/{lang}/menu.{type}.{position}.json
  php run.php --get=core_json-validate --json="$json" --schema_id=urn:equal:json-schema:core:menu.translations
  ```
- **Note**: No dedicated menu translation consistency controller exists yet. Avoid inline JSON in PowerShell; pass content through a UTF-8 variable if direct validation is required.

### API Routes
- **Schema ID**: `urn:equal:json-schema:core:api.route`
- **Usage**: Validate API route definitions
- **File pattern**: `packages/{package}/init/routes/{priority}-{name}.json`
- **Validation example**:
  ```
  php run.php --do=core_test_route-consistency --package={package} --file={priority-name.json}
  ```

### Package Manifest
- **Schema ID**: `urn:equal:json-schema:core:package.manifest`
- **Usage**: Validate package manifest files
- **File pattern**: `packages/{package}/manifest.json`
- **Preferred validation**: Use `core_test_manifest-consistency`; it loads and validates the manifest internally.
- **Fallback only**: Use `packageinfo` plus `core_json-validate` only when the dedicated consistency controller is unavailable.
- **Validation example**:
  ```
  php run.php --do=core_test_manifest-consistency --package={package}
  ```

## Validation Procedure

### For Model Views
Prefer the dedicated view consistency controller. It retrieves the view and validates it internally, avoiding fragile shell escaping for full JSON payloads.

```
./equal.run --do=core_test_view-consistency --entity={EntityName} --view_id={type}.{name}
```

When running from PowerShell, use the PHP entry point:

```
php run.php --do=core_test_view-consistency --entity={EntityName} --view_id={type}.{name}
```

### For Other Supported JSON Files
Prefer the dedicated consistency controller for the file type:

```
php run.php --do=core_test_translation-consistency --entity={EntityName} --lang={lang}
php run.php --do=core_test_menu-consistency --package={package} --menu_id={app}.{position}
php run.php --do=core_test_route-consistency --package={package} --file={priority-name.json}
php run.php --do=core_test_manifest-consistency --package={package}
```

### Fallback for Raw JSON Files Without a Dedicated Controller
Use this fallback only when no dedicated consistency controller exists. Do not use it for model views, dashboard views, model translations, menu definitions, route files, or package manifests.

1. Read the created JSON file only because no consistency controller covers that file type.
2. Run `./equal.run --get=core_json-validate` data action with:
   - `--json` parameter: file contents as JSON string
   - `--schema_id` parameter: appropriate schema from table above
   - `--strict=false` for lenient validation (allows missing optional fields)

When running from PowerShell, first validate JSON syntax with `Get-Content -Raw -Encoding UTF8 <file> | ConvertFrom-Json | Out-Null`, then pass file contents through a UTF-8 variable as described in `AGENTS/00-general/POWERSHELL.md`. This local JSON syntax check is only troubleshooting support; it is not a substitute for eQual consistency validation. Do not embed full JSON directly in the command line.

### For PHP-Backed Metadata Without a Dedicated Controller
1. Do not parse PHP files manually.
2. Ask eQual for the metadata representation using the appropriate controller:
   - `core_model_schema` for model classes.
   - `--announce=true` for action handlers and data providers.
3. Call `core_json-validate` with the returned JSON representation only when no dedicated consistency controller exists for that metadata.

## When to Validate

- **Required**: Always validate newly created structural files
- **When**: After file creation or modification, before marking task complete
- **Where**: Include validation results in agent output to confirm success
- **Expected**: All validations must pass (no errors reported)

## Common Validation Errors

| Error | Cause | Fix |
|-------|-------|-----|
| Required field missing | Schema requires a mandatory field not provided | Add the missing field |
| Invalid type | Field value type doesn't match schema | Convert to correct type |
| Unknown property | Field name not recognized | Remove or rename to valid field |
| Pattern mismatch | String doesn't match required format | Adjust value to match pattern |
| Reference error | Referenced entity/field doesnt exist | Correct the reference |
