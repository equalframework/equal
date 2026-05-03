# Agent Routing Guide

## Role and boundaries
- Agents assist human developers and must not merge pull requests.
- Agents must work within a single package per task.
- The `core` package is off-limits and must not be edited.

## Mandatory workflow
1. Identify the task type before editing files.
2. Read the required instruction files from the routing table.
3. If a task impacts multiple layers (for example class + view + i18n), read every matching task folder before making changes.
4. Finish every task by running:
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
| Create action or data handler | `AGENTS/50-create-action-or-data-handler/INSTRUCTIONS.md`, `AGENTS/50-create-action-or-data-handler/EXAMPLES.md`, `AGENTS/50-create-action-or-data-handler/VALIDATION.md` |

For detailed eQual framework conventions, see `AGENTS/AGENTS_REFERENCE.md`.
