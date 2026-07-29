# Validation checklist — Update field

## Structure Validation

- [ ] The field exists (or is intentionally introduced/removed) in the intended entity.
- [ ] The field type and options are valid for the business intent.
- [ ] Relation updates point to existing entities/fields and preserve consistency.
- [ ] Computed/derived field dependencies were updated and remain complete.
- [ ] All impacted view references (form/list/search/filter) are valid.
- [ ] Form views were updated when the field affects data entry.
- [ ] List views were updated when the field affects table visibility or sorting.
- [ ] Related filters, actions, data providers, exports, and routes were reviewed.
- [ ] Translation entries were updated in every supported language.
- [ ] Impacted field translations include `label`, `description`, and `help`.
- [ ] Removed/renamed fields no longer have stale references in code, views, or i18n.
- [ ] No unrelated field changes were introduced in the same entity/package.
- [ ] If a `.class.php` file was modified, database access was checked with `./equal.run --do=test_db-access`, or the environment limitation was recorded.
- [ ] If a `.class.php` file was modified and the configured database was missing, `config/config.json` was confirmed valid and `./equal.run --do=init_db` was run.
- [ ] If a `.class.php` file was modified, the impacted package was reinitialized with `./equal.run --do=init_package --package={package} --force=true`.

## Consistency and Metadata Validation (when applicable)

If views, models or translation files were modified as part of the field update, validate (for specifics about validation procedures see `AGENTS/00-general/VALIDATION-SCHEMAS.md`) them:

- [ ] **View consistency validation** (if views modified):
  - For each modified form/list/search/chart view, run `php run.php --do=core_test_view-consistency --entity={EntityName} --view_id={type}.{name}`.
  - For each modified dashboard view, run `php run.php --do=core_test_view-consistency --entity={EntityName} --view_id=dashboard.{name}` or `php run.php --do=core_test_dashboard-consistency --entity={EntityName} --view_id=dashboard.{name}`.
  - Do not read the full view JSON and pass it to `core_json-validate`; use the dedicated view consistency controller.
  - See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for procedures and schema ID references.

- [ ] **Model class validation** (if class modified):
  - Use schema `urn:equal:json-schema:core:model.class`
  - Do not parse the updated `.class.php` file manually.
  - Ask eQual to export the field definitions through controller `core_model_schema`: `./equal.run --get=core_model_schema --entity={EntityName}`
  - Validate the returned JSON representation with `core_json-validate`:
    - `json` parameter: the JSON representation of the updated model class
    - `schema_id` parameter: `urn:equal:json-schema:core:model.class`
    - `package` parameter: the package name where the entity is defined
  - Avoid embedding the JSON representation directly in a shell command; see `AGENTS/00-general/POWERSHELL.md` for PowerShell-safe handling
  - Confirm: no validation errors returned

- [ ] **Translation schema validation** (if i18n files modified):
  - Model translations: Use schema `urn:equal:json-schema:core:model.translations`
  - Prefer `php run.php --do=core_test_translation-consistency --entity={EntityName} --lang={lang}` for each modified model translation file
  - Confirm: no validation errors returned
