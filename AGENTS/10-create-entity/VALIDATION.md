# Validation checklist — Create entity

## Structure Validation

- [ ] The new entity class was created at the expected ORM path for the target package.
- [ ] The namespace matches the file path and the class name matches the file name.
- [ ] Entity metadata/configuration follows package ORM conventions.
- [ ] Every declared field has a valid type and coherent options.
- [ ] Relation fields target existing entities and valid target fields.
- [ ] Computed/derived fields declare complete and valid dependencies.

## JSON Schema Validation

- [ ] **Model class validation**: Run `php run.php --get=core_json-validate --json={json_string} --schema_id=urn:equal:json-schema:core:model.class --package={package}` with:
  - Extract the ORM field definitions from the created `.class.php` file and convert to JSON representation through `php run.php --get=model_export --package={package} --entity={EntityName}`
  - Use schema ID: `urn:equal:json-schema:core:model.class`
  - Use the package name where the entity was created
  - Confirm: no validation errors returned

## View Validation

- [ ] Required default form and/or list views were created when the entity is user-facing.
- [ ] Newly created views reference only existing fields.
- [ ] **Form view schema validation** (if created): Use schema `urn:equal:json-schema:core:view.form`
- [ ] **List view schema validation** (if created): Use schema `urn:equal:json-schema:core:view.list`
- [ ] **Dashboard view schema validation** (if created): Use schema `urn:equal:json-schema:core:view.dashboard`
- [ ] **Search view schema validation** (if created): Use schema `urn:equal:json-schema:core:view.search`
- [ ] **Chart view schema validation** (if created): Use schema `urn:equal:json-schema:core:view.chart`
- [ ] See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for detailed validation procedures

## Translation Validation

- [ ] Translation files were created or updated for all supported languages.
- [ ] Field translations include `label`, `description`, and `help` where required.
- [ ] View names and section identifiers introduced by this entity are translated.
- [ ] **Translation schema validation**: Use schema `urn:equal:json-schema:core:model-translations` for each translation file

## General

- [ ] No unrelated entity definition was modified.
