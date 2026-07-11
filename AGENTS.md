# Agent Routing Guide

## Role and boundaries
Unless explicitly told otherwise:
- Agents assist human developers and must not merge pull requests.
- Agents must work within a single package per task.
- The `packages/core` package is off-limits and must not be edited.
- Framework internals under `lib/equal/**`, `public/**`, and framework tests are only in scope for tasks routed as framework internals.

## Mandatory workflow
1. Identify the task type before editing files.
2. Read the required instruction files from the routing table.
3. When running commands from PowerShell, read and follow `AGENTS/00-general/POWERSHELL.md`.
4. If a task impacts multiple layers (for example class + view + i18n), read every matching task folder before making changes.
5. For model behavior involving hooks, synchronization logic, ORM actions, or public helper APIs, follow the related conventions in `AGENTS/AGENTS_REFERENCE.md`.
6. Documentation examples may use `./equal.run`; when executing commands from PowerShell, run the equivalent `php run.php ...` command from the project root.
7. For any task that creates or modifies `packages/**/classes/*.class.php`, run `./equal.run --do=test_db-access` before package initialization. If it exits `0`, continue. If the configured database does not exist, ensure `config/config.json` exists and is valid, then run `./equal.run --do=init_db`.
8. After any `packages/{package}/classes/*.class.php` change, reinitialize the impacted package with `./equal.run --do=init_package --package={package} --force=true`.
9. Finish every task by running:
   - the task-specific `VALIDATION.md`
   - `AGENTS/00-general/VALIDATION.md`
   - `AGENTS/90-final-validation/VALIDATION.md`

## Task routing table
| Task type | Required files |
| --- | --- |
| Create entity | `AGENTS/10-create-entity/INSTRUCTIONS.md`, `AGENTS/10-create-entity/EXAMPLES.md`, `AGENTS/10-create-entity/VALIDATION.md` |
| Update field | `AGENTS/20-update-field/INSTRUCTIONS.md`, `AGENTS/20-update-field/EXAMPLES.md`, `AGENTS/20-update-field/VALIDATION.md` |
| Create or update view | `AGENTS/30-create-or-update-view/INSTRUCTIONS.md`, `AGENTS/30-create-or-update-view/EXAMPLES.md`, `AGENTS/30-create-or-update-view/VALIDATION.md` |
| Update translations | `AGENTS/40-update-translations/INSTRUCTIONS.md`, `AGENTS/40-update-translations/EXAMPLES.md`, `AGENTS/40-update-translations/VALIDATION.md` |
| Create action handler | `AGENTS/50-create-action-handler/INSTRUCTIONS.md`, `AGENTS/50-create-action-handler/EXAMPLES.md`, `AGENTS/50-create-action-handler/VALIDATION.md` |
| Create data provider | `AGENTS/60-create-data-provider/INSTRUCTIONS.md`, `AGENTS/60-create-data-provider/EXAMPLES.md`, `AGENTS/60-create-data-provider/VALIDATION.md` |
| Framework internals | `AGENTS/70-framework-internals/INSTRUCTIONS.md`, `AGENTS/70-framework-internals/EXAMPLES.md`, `AGENTS/70-framework-internals/VALIDATION.md` |
| Bugfix or refactor in package code | Read the task folder for every impacted component, plus `AGENTS/00-general/INSTRUCTIONS.md` and `AGENTS/00-general/VALIDATION.md` |
| Workflow/status transition | `AGENTS/50-create-action-handler/INSTRUCTIONS.md`, `AGENTS/30-create-or-update-view/INSTRUCTIONS.md`, `AGENTS/40-update-translations/INSTRUCTIONS.md` when UI or i18n is impacted |
| Menus, routes, manifest, or package metadata | `AGENTS/00-general/INSTRUCTIONS.md`, `AGENTS/00-general/VALIDATION-SCHEMAS.md`, `AGENTS/00-general/VALIDATION.md` |

For focused references, start with `AGENTS/reference/README.md`. For the complete eQual framework reference, see `AGENTS/AGENTS_REFERENCE.md`.
