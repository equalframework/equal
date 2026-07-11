1. Code identifiers, variables, and comments must be written in English.
2. Agents must not merge pull requests.
3. Agents must not modify the `packages/core` package unless the user explicitly asks for that package.
4. Agents must work inside the active package only for package tasks; framework internals tasks must stay inside the identified framework subsystem.
5. Agents must identify the impacted package before editing package files, or the impacted framework subsystem before editing framework files.
6. Agents must not assume that changing a class is sufficient.
7. Agents must always check the impact on `classes/`, `views/`, `i18n/`, `actions/`, and `data/`.
8. Agents must preserve namespace and path consistency.
9. Agents must avoid inventing framework conventions.
10. Agents must prefer existing patterns already present in the package.
11. Agents must keep changes minimal and related to the requested task.
12. Before inventing a CLI command or manually reading many files, agents must look for a framework controller that already exposes the requested information.
13. Use `./equal.run --get=core_config_controllers --package={package}` to discover available controllers for a package, then select the controller whose name and announcement match the requested information.
14. Prefer discovery/configuration controllers such as `core_config_packages`, `core_config_classes`, `core_config_views`, `core_config_controllers`, `core_config_routes`, `core_config_i18n`, and `core_packageinfo` when looking up framework metadata.
15. Inspect a candidate controller contract with `./equal.run --get={controller} --announce=true` or `./equal.run --do={controller} --announce=true`; use the declared `params` to build the final command.
16. For controller files, agents must derive the CLI/PHP controller name from the file path: `packages/{package}/actions/{path}/{name}.php` becomes `{package}_{path_with_underscores}_{name}` and is called with `--do`; `packages/{package}/data/{path}/{name}.php` becomes `{package}_{path_with_underscores}_{name}` and is called with `--get`.
17. When a controller path has no subdirectory, the controller name is only `{package}_{name}`; when it has nested directories, replace each `/` path separator with `_`.
18. Use `./equal.run --do={controller}` for action handlers and `./equal.run --get={controller}` for data providers; pass controller params as CLI flags with the same names declared in `eQual::announce()`.
19. Use `--announce=true` to inspect or validate a controller contract without executing its business logic after `eQual::announce()`.
20. When one controller must call another, use `eQual::run('do', '{controller}', $params)` for action handlers and `eQual::run('get', '{controller}', $params)` for data providers; do not duplicate the target controller logic.
21. When testing a controller that calls another controller, verify both the caller behavior and the target controller contract, using `--announce=true` on the target when only its declared params/response need to be checked.
22. For model synchronization logic, prefer a named ORM action triggered with `$self->do('action_name')` from hooks instead of a private helper call.
23. Do not add public model helper methods unless an existing consumer or stable cross-entity API clearly requires them.

## Framework and package boundaries

24. Treat `packages/core/**` as application package code that is off-limits unless explicitly requested.
25. Treat `lib/equal/**`, `public/**`, and framework tests as framework internals; use `AGENTS/70-framework-internals/INSTRUCTIONS.md` for those tasks.
26. If a task appears to require both framework internals and package files, keep the two concerns separate and report both scopes explicitly.
27. Do not use package-specific behavior to justify a framework-level change unless the framework contract is actually wrong.

## Common anti-patterns

28. Do not add a public helper method for one narrow caller; prefer existing model actions, policies, providers, or local private methods as appropriate.
29. Do not put write-side workflows in data providers.
30. Do not create an HTTP action handler when an inline ORM action, workflow transition, or existing controller is the correct entry point.
31. Do not call `update()` from computed field logic.
32. Do not duplicate controller or model logic that can be reached through `eQual::run()` or `$self->do()`.
33. Do not silently change error keys, response shapes, HTTP statuses, or public method signatures.
34. Do not broaden a task into unrelated cleanup or refactoring.

## Final response format

35. Final responses must distinguish what changed, what validation ran, and what validation was skipped.
36. When reporting commands, use the command actually executed. In PowerShell this usually means `php run.php ...`, not the documentation form `./equal.run ...`.
