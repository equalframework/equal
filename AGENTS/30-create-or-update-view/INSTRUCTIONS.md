1. Identify the target package.
2. Confirm the work stays within a single package and does not touch `core`.
3. Identify the target entity, namespace, and the exact folder path it implies under `views/`, `i18n/`, `actions/`, and `data/`.
4. Inspect nearby existing views in the same package and namespace for naming, layout, widget, filter, action, and translation conventions.
5. Identify the view type: form, list, chart, dashboard, or another supported view type.
6. Locate the corresponding ORM class.
7. Create or update the relevant file under `{package}/views/{namespace}/`; for default views, use `{Entity}.form.default.json` or `{Entity}.list.default.json`, and for other view types follow the package naming convention.
8. Use only fields that exist in the ORM class, including relation fields referenced by widgets, filters, routes, or nested views.
9. For form views, structure the layout with sensible groups, sections, rows, and columns based on nearby views.
10. Add section `id`s in form views when the UI needs translated tab or section labels.
11. Configure relation widgets, nested views, domains, routes, and header actions only when needed.
12. For list views, define useful columns, default sorting, filters, grouping, exports, and selection or header actions only when justified by the feature.
13. For chart, dashboard, or other view types, follow nearby package conventions and keep every referenced field, action, route, filter, and export valid.
14. Ensure every section ID has a translation.
15. Update view translations in every language already supported by the package, not a hardcoded language set.
16. Place translations at `{package}/i18n/{lang}/{namespace}/{Entity}.json` when the namespace maps to subfolders.
17. Translate every impacted view entry, including view `name`, `description`, `actions`, `routes`, `exports`, and `layout.section.*` labels.
18. Remove obsolete fields, actions, routes, filters, exports, or layout entries when the view is being updated.
19. Preserve existing layout and file naming conventions from nearby views.
20. Validate JSON syntax and run a final consistency pass on filenames, translation keys, section IDs, and referenced actions.
21. Refer to `AGENTS_REFERENCE.md` for detailed view conventions.
