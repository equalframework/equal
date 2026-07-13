# Validation reference

Use this reference before finishing any task.

Required structure:

- Run the task-specific `VALIDATION.md`.
- Run `AGENTS/00-general/VALIDATION.md`.
- Run `AGENTS/90-final-validation/VALIDATION.md`.
- Report commands exactly as executed.
- Report skipped validation with the reason.

PowerShell command rule:

- Documentation examples may use `./equal.run`.
- Actual PowerShell execution should use `php run.php ...` from the project root.

Prefer dedicated consistency controllers:

- Views: `core_test_view-consistency`
- Dashboards: `core_test_dashboard-consistency`
- Model translations: `core_test_translation-consistency`
- Menus: `core_test_menu-consistency`
- Package routes: `core_test_route-consistency`
- Package manifests: `core_test_manifest-consistency`

Use `core_json-validate` only when no dedicated consistency controller exists.
