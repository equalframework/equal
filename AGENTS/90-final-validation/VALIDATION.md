# Validation checklist — Final cross-layer review

## Scope and Syntax

- [ ] All modified files are inside the intended package, or inside the identified framework subsystem for framework internals tasks.
- [ ] The `packages/core` package was not modified unless explicitly requested.
- [ ] PHP syntax is valid.
- [ ] JSON syntax is valid.

## References and Consistency

- [ ] Namespaces match file paths.
- [ ] Every entity field referenced in impacted views exists.
- [ ] If a class schema or any other `.class.php` behavior changed, database access was checked with `./equal.run --do=test_db-access`.
- [ ] If a class schema or any other `.class.php` behavior changed and the configured database was missing, `config/config.json` was confirmed valid and `./equal.run --do=init_db` was run.
- [ ] If a class schema or any other `.class.php` behavior changed, the impacted package was reinitialized with `./equal.run --do=init_package --package={package} --force=true`.
- [ ] Translations exist for every impacted supported language.
- [ ] Form/list section identifiers used in views are translated.
- [ ] Actions and data providers referenced in views exist.
- [ ] Action/provider labels and user-visible errors are translated.
- [ ] Obsolete field/view/i18n references were removed.

## Schema Validation

- [ ] All newly created JSON files have been validated against their respective schemas
- [ ] Refer to task-specific VALIDATION.md files for schema validation details
- [ ] Refer to `AGENTS/00-general/VALIDATION-SCHEMAS.md` for schema IDs and procedures
- [ ] Prefer dedicated consistency controllers instead of passing full JSON through CLI arguments:
  - Views: `core_test_view-consistency`
  - Dashboards: `core_test_dashboard-consistency` or `core_test_view-consistency` with `dashboard.{name}`
  - Model translations: `core_test_translation-consistency`
  - Menus: `core_test_menu-consistency`
  - Package routes: `core_test_route-consistency`
- [ ] Use `core_json-validate` only for schemas that do not yet have a dedicated consistency controller, such as model class definitions, menu translations, and action/data-provider metadata.

## Code Quality

- [ ] No unnecessary unrelated changes were introduced.
- [ ] Existing package or framework subsystem conventions were respected.
- [ ] The final diff is coherent and reviewable end-to-end.
- [ ] The final response separates changes, validation run, and validation skipped.
