# Validation checklist — Final cross-layer review

## Scope and Syntax

- [ ] All modified files are inside the intended package.
- [ ] The `core` package was not modified.
- [ ] PHP syntax is valid.
- [ ] JSON syntax is valid.

## References and Consistency

- [ ] Namespaces match file paths.
- [ ] Every entity field referenced in impacted views exists.
- [ ] Translations exist for every impacted supported language.
- [ ] Form/list section identifiers used in views are translated.
- [ ] Actions and data providers referenced in views exist.
- [ ] Action/provider labels and user-visible errors are translated.
- [ ] Obsolete field/view/i18n references were removed.

## Schema Validation

- [ ] All newly created JSON files have been validated against their respective schemas
- [ ] Refer to task-specific VALIDATION.md files for schema validation details
- [ ] Refer to `AGENTS/00-general/VALIDATION-SCHEMAS.md` for schema IDs and procedures
- [ ] Use `php run.php --get=core_json-validate` with appropriate schema IDs for:
  - Views (form, list, dashboard, search, menu)
  - Translations (model, menu)
  - Entities (model class definitions)
  - Actions/Data providers (controller action metadata)

## Code Quality

- [ ] No unnecessary unrelated changes were introduced.
- [ ] Existing package conventions were respected.
- [ ] The final diff is coherent and reviewable end-to-end.
