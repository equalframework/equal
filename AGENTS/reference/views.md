# Views reference

Use this reference for `views/*.json`, menus, dashboards, charts, search views, form views, and list views.

Key rules:

- Use fields, actions, routes, filters, and exports that actually exist.
- Preserve nearby package naming, widget, layout, route, and action conventions.
- For form views, translate every section `id`.
- For list views, add sorting, filters, grouping, exports, and selection actions only when justified by the workflow.
- Remove obsolete references when fields, actions, or routes are renamed or deleted.
- Prefer dedicated consistency controllers over direct `core_json-validate` when available.

Validation details live in `AGENTS/00-general/VALIDATION-SCHEMAS.md`.
