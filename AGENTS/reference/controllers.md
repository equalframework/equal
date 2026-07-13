# Controllers reference

Use this reference for `actions/`, `data/`, inline ORM actions, workflow transitions, and controller calls.

Key rules:

- Choose the right mechanism before writing code:
  - `getActions()` for actions carried by selected model objects.
  - `getWorkflow()` plus `core_model_transition` for status transitions.
  - `actions/` for autonomous or mutating HTTP/controller endpoints.
  - `data/` for read/query endpoints.
- Derive controller CLI names from file paths and inspect contracts with `--announce=true`.
- Declare controller params, providers, access, response, and constants through `eQual::announce()`.
- Use `eQual::run('do', ...)` or `eQual::run('get', ...)` instead of duplicating controller logic.
- Keep write-side workflows out of data providers.
- Use stable business error keys and translate user-visible errors.

In PowerShell, execute controller commands with `php run.php --do=...` or `php run.php --get=...`.
