# ORM reference

Use this reference for `classes/*.class.php`, ORM hooks, synchronization logic, computed fields, model actions, and collection semantics.

Key rules:

- Prefer existing package model patterns before introducing new abstractions.
- Use `getColumns()` for fields and keep field metadata coherent with nearby entities.
- Avoid side effects in computed fields, especially `update()` calls.
- For synchronization logic, prefer a named ORM action in `getActions()` and trigger it with `$self->do('action_name')` from hooks.
- Avoid new public helper methods unless a stable existing consumer or cross-entity API requires them.
- Preserve policy, error-key, hook, and action behavior when changing existing models.
- After changing `packages/{package}/classes/*.class.php`, check DB access and reinitialize the impacted package as described in `AGENTS.md`.

For framework ORM internals under `lib/equal/orm/**`, use `AGENTS/70-framework-internals/INSTRUCTIONS.md`.
