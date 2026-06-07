1. Code identifiers, variables, and comments must be written in English.
2. Agents must not merge pull requests.
3. Agents must not modify the `core` package.
4. Agents must work inside the active package only.
5. Agents must identify the impacted package before editing files.
6. Agents must not assume that changing a class is sufficient.
7. Agents must always check the impact on `classes/`, `views/`, `i18n/`, `actions/`, and `data/`.
8. Agents must preserve namespace and path consistency.
9. Agents must avoid inventing framework conventions.
10. Agents must prefer existing patterns already present in the package.
11. Agents must keep changes minimal and related to the requested task.
12. For controller files, agents must derive the CLI/PHP controller name from the file path: `packages/{package}/actions/{path}/{name}.php` becomes `{package}_{path_with_underscores}_{name}` and is called with `--do`; `packages/{package}/data/{path}/{name}.php` becomes `{package}_{path_with_underscores}_{name}` and is called with `--get`.
13. When a controller path has no subdirectory, the controller name is only `{package}_{name}`; when it has nested directories, replace each `/` path separator with `_`.
14. Use `php run.php --do={controller}` for action handlers and `php run.php --get={controller}` for data providers; pass controller params as CLI flags with the same names declared in `eQual::announce()`.
15. Use `--announce=true` to inspect or validate a controller contract without executing its business logic after `eQual::announce()`.
16. When one controller must call another, use `eQual::run('do', '{controller}', $params)` for action handlers and `eQual::run('get', '{controller}', $params)` for data providers; do not duplicate the target controller logic.
17. When testing a controller that calls another controller, verify both the caller behavior and the target controller contract, using `--announce=true` on the target when only its declared params/response need to be checked.
