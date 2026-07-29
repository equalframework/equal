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

## Consistency Validation

Validate the created or updated view through eQual's dedicated consistency controllers. These controllers load the view internally and validate it with the framework's own view consistency rules.

- For model views, run `./equal.run --do=core_test_view-consistency --entity={EntityName} --view_id={type}.{name}`.
- In PowerShell, prefer `php run.php --do=core_test_view-consistency --entity={EntityName} --view_id={type}.{name}`.
- For dashboard views, `core_test_view-consistency` accepts `dashboard.{name}`; `core_test_dashboard-consistency` is also available when an explicit dashboard-only check is clearer.
- For menu views, run `php run.php --do=core_test_menu-consistency --package={package} --menu_id={app}.{position}` instead of passing the JSON through the CLI.
- Do not read the full view JSON and pass it to `core_json-validate`; use `core_json-validate` only for raw JSON files that have no dedicated consistency controller.
- Confirm no validation errors are returned.
- See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for detailed procedures.

## Schema IDs Reference

These schema IDs are references for interpreting errors and for unsupported fallback validation only:

- **Form view** (`*.form.*.json`): `urn:equal:json-schema:core:view.form`
- **List view** (`*.list.*.json`): `urn:equal:json-schema:core:view.list`
- **Dashboard view** (`*.dashboard.*.json`): `urn:equal:json-schema:core:view.dashboard`
- **Search view** (`*.search.*.json`): `urn:equal:json-schema:core:view.search`
- **Menu view** (`menu.*.*.json`): `urn:equal:json-schema:core:menu`
