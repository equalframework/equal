# JSON Schema Validation for Agent-Created Content

When agents create or modify content, they must validate the structure using available JSON schemas.

## Schema Reference by Component Type

### Entities (Model Classes)
- **Schema ID**: `urn:equal:json-schema:core:model.class`
- **Usage**: Validate the JSON representation of ORM model class definitions
- **Required fields**: `name`, `fields`
- **Convert the PHP file** structure to JSON representation with `php run.php --get=model_export --entity={EntityName}`
- **Validate** through `core_json-validate` with the JSON representation and schema ID
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={"name":"Post","fields":{...}} --schema_id=urn:equal:json-schema:core:model.class
  ```

### Views - Form
- **Schema ID**: `urn:equal:json-schema:core:view.form`
- **Usage**: Validate form view files (Model.form.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.form.*.json`
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={view_contents} --schema_id=urn:equal:json-schema:core:view.form
  ```

### Views - List
- **Schema ID**: `urn:equal:json-schema:core:view.list.default`
- **Usage**: Validate list/table view files (Model.list.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.list.*.json`
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={view_contents} --schema_id=urn:equal:json-schema:core:view.list.default
  ```

### Views - Dashboard
- **Schema ID**: `urn:equal:json-schema:core:view.dashboard.default`
- **Usage**: Validate dashboard view files (*.dashboard.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.dashboard.*.json`
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={view_contents} --schema_id=urn:equal:json-schema:core:view.dashboard.default
  ```

### Views - Search
- **Schema ID**: `urn:equal:json-schema:core:view.search.default`
- **Usage**: Validate search/filter view files (*.search.*.json)
- **Required fields**: `name`, `layout`
- **File pattern**: `packages/{package}/views/{EntityName}.search.*.json`
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={view_contents} --schema_id=urn:equal:json-schema:core:view.search.default
  ```

### Menus
- **Schema ID**: `urn:equal:json-schema:core:menu.default`
- **Usage**: Validate menu definition files
- **Required fields**: `layout`
- **File pattern**: `packages/{package}/views/menu.{app}.{position}.json`
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={menu_contents} --schema_id=urn:equal:json-schema:core:menu.default
  ```

### Action Handlers
- **Schema ID**: `urn:equal:json-schema:core:controller.action`
- **Usage**: Validate action handler definitions (JSON representation of PHP action files)
- **Required fields**: `type`, `name`, `package_name`
- **File pattern**: `packages/{package}/actions/{path}/{action}.php`
- **Get JSON representation**: Use `php run.php --do={package}_{path}_{action} --announce=true` to extract the action metadata as JSON`
- **Validate** through `core_json-validate` with the JSON representation and schema ID
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={action_json} --schema_id=urn:equal:json-schema:core:controller.action
  ```

### Data Providers
- **Schema ID**: `urn:equal:json-schema:core:controller.action`
- **Usage**: Same as action handlers; data providers are validated as controller actions
- **Required fields**: `type`, `name`, `package_name`
- **File pattern**: `packages/{package}/data/{path}/{provider}.php`
- **Get JSON representation**: Use `php run.php --get={package}_{path}_{provider} --announce=true` to extract the provider metadata as JSON`
- **Validate** through `core_json-validate` with the JSON representation and schema ID
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={provider_json} --schema_id=urn:equal:json-schema:core:controller.action
  ```

### Model Translations
- **Schema ID**: `urn:equal:json-schema:core:model-translations`
- **Usage**: Validate model/entity translation files
- **File pattern**: `packages/{package}/i18n/{lang}/{EntityName}.json`
- **Contains**: Field labels, descriptions, help text, view translations, and error messages
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={translation_contents} --schema_id=urn:equal:json-schema:core:model-translations
  ```

### Menu Translations
- **Schema ID**: `urn:equal:json-schema:core:menu-translations`
- **Usage**: Validate menu translation files
- **File pattern**: `packages/{package}/i18n/{lang}/menu.{type}.{position}.json`
- **Contains**: Menu item labels, descriptions, and layout translations
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={menu_translation_contents} --schema_id=urn:equal:json-schema:core:menu-translations
  ```

### API Routes
- **Schema ID**: `urn:equal:json-schema:core:api.route`
- **Usage**: Validate API route definitions
- **File pattern**: `config/routing/{priority}-{name}.json`
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={route_contents} --schema_id=urn:equal:json-schema:core:api.route
  ```

### Package Manifest
- **Schema ID**: `urn:equal:json-schema:core:package.manifest`
- **Usage**: Validate package manifest files
- **File pattern**: `packages/{package}/manifest.json`
- **Get JSON representation**: Use `php run.php --get=packageinfo --package={package}` to get the manifest contents as JSON
- **Validate** through `core_json-validate` with the JSON representation and schema ID
- **Validation example**:
  ```
  php run.php --get=core_json-validate --json={manifest_contents} --schema_id=urn:equal:json-schema:core:package.manifest
  ```

## Validation Procedure

### For JSON Files
1. Read the created JSON file
2. Run `php run.php --get=core_json-validate` data action with:
   - `--json` parameter: file contents as JSON string
   - `--schema_id` parameter: appropriate schema from table above
   - `--strict=false` for lenient validation (allows missing optional fields)

### For PHP Files (Actions, Data Providers)
1. Convert the PHP file structure to JSON representation using the appropriate php command (e.g., `model_export` for entities, `--announce=true` for actions/providers or `packageinfo` for packages)
2. Call `core_json-validate` with the JSON representation

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
