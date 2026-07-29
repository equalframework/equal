# Validation checklist — Create entity

## Structure Validation

- [ ] The new entity class was created at the expected ORM path for the target package.
- [ ] The namespace matches the file path and the class name matches the file name.
- [ ] Entity metadata/configuration follows package ORM conventions.
- [ ] Every declared field has a valid type and coherent options.
- [ ] Relation fields target existing entities and valid target fields.
- [ ] Computed/derived fields declare complete and valid dependencies.

## JSON Schema Validation

- [ ] **Model class validation**:
  - Extract the ORM field definitions from the created `.class.php` file and convert to JSON representation through controller `core_model_schema`: `./equal.run --get=core_model_schema --entity={EntityName}`
  - Validate that JSON representation with schema ID `urn:equal:json-schema:core:model.class` and the package name where the entity was created
  - Avoid embedding the JSON representation directly in a shell command; see `AGENTS/00-general/POWERSHELL.md` for PowerShell-safe handling
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
- [ ] **Translation schema validation**: Use `php run.php --do=core_test_translation-consistency --entity={EntityName} --lang={lang}` for each translation file

## General

- [ ] Database access was checked with `./equal.run --do=test_db-access`, or the environment limitation was recorded.
- [ ] If the configured database was missing, `config/config.json` was confirmed valid and `./equal.run --do=init_db` was run.
- [ ] The impacted package was reinitialized with `./equal.run --do=init_package --package={package} --force=true`.
- [ ] No unrelated entity definition was modified.
