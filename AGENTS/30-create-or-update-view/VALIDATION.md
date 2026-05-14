# Validation checklist — Create or update view

## Structure Validation

- [ ] The view file path and naming follow package conventions.
- [ ] JSON syntax is valid and the document structure matches expected schema.
- [ ] The referenced entity exists and matches the intended business object.
- [ ] Every referenced field exists in the corresponding ORM/entity class.
- [ ] Form layout structure (sections, groups, widgets) is valid and coherent.
- [ ] List layout structure (columns, sort/filter config) is valid and coherent.
- [ ] Referenced actions/data providers/routes/exports exist and are compatible.
- [ ] View labels/titles are translated in supported languages.
- [ ] Section identifiers used in the view are translated.
- [ ] No obsolete fields or stale action references remain in the updated view.
- [ ] No unrelated views were modified.

## JSON Schema Validation

Validate the created/updated view file using the appropriate schema based on view type:

- [ ] **Form view** (`*.form.*.json`): Use schema `urn:equal:json-schema:core:view.form`
- [ ] **List view** (`*.list.*.json`): Use schema `urn:equal:json-schema:core:view.list`
- [ ] **Dashboard view** (`*.dashboard.*.json`): Use schema `urn:equal:json-schema:core:view.dashboard`
- [ ] **Search view** (`*.search.*.json`): Use schema `urn:equal:json-schema:core:view.search`
- [ ] **Menu view** (`menu.*.*.json`): Use schema `urn:equal:json-schema:core:menu.default`

**Validation procedure**:
- Run `php run.php --get=core_json-validate` with:
  - `json` parameter: the complete view file content as JSON
  - `schema_id` parameter: the appropriate schema ID from above
  - `package` parameter: the package name where the view is located
  - Confirm: no validation errors returned
  - See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for detailed procedures
