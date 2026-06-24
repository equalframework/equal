1. Identify the target package.
2. Confirm the work stays within a single package and does not touch `core`.
3. Identify the target namespace and the exact folder path it implies under `classes/`, `views/`, `actions/`, `data/`, and `i18n/`.
4. Inspect nearby existing entity classes in the same package and namespace for inheritance, field naming, relation naming, validation, and view conventions.
5. Determine whether the entity must extend `equal\\orm\\Model` directly or an existing package/core class.
6. Identify whether the entity is standalone, a line/detail entity, a taxonomy/reference entity, or a workflow/business entity.
7. Define the class in `{package}/classes/{namespace}/{Entity}.class.php`.
8. Implement `getColumns()` using the package conventions for field names, descriptions, usages, selections, defaults, and relations.
9. Add a human-readable `name` field strategy if package conventions expect one, including a stored computed field when appropriate.
10. Define required scalar fields first, then relations (`many2one`, `one2many`, `many2many`), then computed or alias fields.
11. Add `description`, `required`, `readonly`, `selection`, `usage`, `default`, `domain`, `visible`, `sort`, and `order` metadata where relevant.
12. Define reverse relations only when they are needed by the feature and can be added without broad unrelated impact.
13. Add computed fields only when needed, and ensure they declare `result_type`, `relation`, `store`, and `dependents` consistently.
14. Avoid side effects inside computed logic, especially `update()` calls.
15. Add uniqueness rules with `getUnique()` when the entity has a natural or business key.
16. Add business validations in `cancreate()`, `canupdate()`, `candelete()`, or dedicated methods when the entity has lifecycle constraints.
17. When the entity needs synchronization logic, expose it as a named ORM action in `getActions()` and trigger it with `$self->do('action_name')` from hooks instead of calling a private helper directly.
18. Avoid adding public helper methods unless an existing consumer or stable cross-entity API clearly requires them.
19. Add entity actions in `getActions()` only when there is an actual business operation to expose.
20. If actions are added, implement the corresponding methods and add their translations.
21. Create the default form view in `{package}/views/{namespace}/{Entity}.form.default.json`.
22. Create the default list view in `{package}/views/{namespace}/{Entity}.list.default.json`.
23. For view conventions and implementation details, follow `AGENTS/30-create-or-update-view/INSTRUCTIONS.md`.
24. Add translations for every existing language folder already supported by the package, not a hardcoded language set.
25. Place translations at `{package}/i18n/{lang}/{namespace}/{Entity}.json` when the namespace maps to subfolders.
26. Translate all model fields with `label`, `description`, and `help`, including technical or invisible fields.
27. Translate every created view entry, including view `name`, `description`, `actions`, `routes`, `exports`, and `layout.section.*` labels.
28. Add custom error messages in the `error` block when validations return business error keys.
29. Add action handlers in `actions/` or data providers in `data/` only if the new entity requires dedicated endpoints or custom data rendering.
30. Add seed/init data only if the entity is a reference entity that must exist by default.
31. Avoid modifying unrelated entities, views, or translations unless a relation or navigation requirement makes it necessary.
32. Run a consistency pass on filenames, namespaces, relation targets, translation paths, and view ids.
33. Before package initialization, run `./equal.run --do=test_db-access`; if the configured database does not exist, ensure `config/config.json` exists and is valid, then run `./equal.run --do=init_db`.
34. Reinitialize the impacted package with `./equal.run --do=init_package --package={package} --force=true` after creating the entity class.
35. Validate the package with `./equal.run --do=test_package-consistency --package={package}` when the environment allows it.
36. If the entity introduces user-facing behavior, document any non-obvious view or workflow behavior in package docs when relevant.
37. Refer to `AGENTS.md` and framework docs under `DOC/docs/application-developers/core-development-guide` for detailed conventions.
