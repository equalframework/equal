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

## JSON Schema Validation (when applicable)

If views, models or translation files were modified as part of the field update, validate (for specifics about validation procedures see `AGENTS/00-general/VALIDATION-SCHEMAS.md`) them:

- [ ] **View schema validation** (if views modified):
  - Form views: Use schema `urn:equal:json-schema:core:view.form`
  - List views: Use schema `urn:equal:json-schema:core:view.list`
  - Dashboard views: Use schema `urn:equal:json-schema:core:view.dashboard`
  - Search views: Use schema `urn:equal:json-schema:core:view.search`
  - Chart views: Use schema `urn:equal:json-schema:core:view.chart`
  - See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for procedures

- [ ] **Model class validation** (if class modified):
  - Use schema `urn:equal:json-schema:core:model.class`
  - Extract the field definitions from the updated `.class.php` file and convert to JSON representation through `php run.php --get=model_export --package={package} --entity={EntityName}`
  - Run `php run.php --get=core_json-validate` with:
    - `json` parameter: the JSON representation of the updated model class
    - `schema_id` parameter: `urn:equal:json-schema:core:model.class`
    - `package` parameter: the package name where the entity is defined
  - Confirm: no validation errors returned

- [ ] **Translation schema validation** (if i18n files modified):
  - Model translations: Use schema `urn:equal:json-schema:core:model-translations`
  - Run `php run.php --get=core_json-validate` for each modified translation file
  - Confirm: no validation errors returned
