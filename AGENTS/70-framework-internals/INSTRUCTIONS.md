# Framework internals instructions

Use these instructions for tasks that modify eQual framework code outside application packages.

## Scope

This task type applies to:

- `lib/equal/**`
- `public/*.php`
- `packages/core/tests/**`
- framework-level scripts, bootstrap code, compatibility layers, and developer tooling

This task type does not make `packages/core` generally editable. Production package code under `packages/core/**` remains off-limits unless the user explicitly asks for a `packages/core` change.

## Required workflow

1. Identify the framework subsystem before editing: ORM, auth, access, controller routing, HTTP/public entry point, CLI, tests, or tooling.
2. Identify whether the change is framework-only or mixed framework/package work.
3. For mixed framework/package work, keep the framework change and package change clearly separated in the final report. If the package side is not necessary to prove the framework fix, avoid editing the package.
4. Inspect nearby framework code and tests before changing behavior. Prefer existing framework conventions over new abstractions.
5. Search for all framework and package callers of the method, class, hook, or public entry point being changed.
6. Treat public framework APIs as stable contracts. Preserve signatures and behavior unless the user explicitly requested a breaking change.
7. For ORM changes, inspect related code in `lib/equal/orm/`, model hooks, collection behavior, and lifecycle tests.
8. For auth/access changes, inspect related code in `lib/equal/auth/`, access policies, session handling, and public entry points.
9. For public entry point changes, inspect `public/`, CLI entry points, routing, bootstrap behavior, and backwards compatibility expectations.
10. Add or update framework-level tests when the change affects observable behavior and a nearby test exists.
11. Avoid using package-specific assumptions to fix framework behavior. Framework code must remain package-agnostic.
12. Avoid adding new global state, static caches, or side effects unless existing framework patterns already require them.
13. Avoid broad refactors while fixing a targeted framework issue.
14. Preserve error keys, exception types, HTTP statuses, and return shapes unless the task explicitly changes the contract.
15. When changing lifecycle, hook, synchronization, ORM action, or collection semantics, document the expected behavior in tests or nearby comments if it is not obvious.

## Validation expectations

Run the narrowest useful validation first, then broader validation when the subsystem risk justifies it.

- Always run `php -l` on touched PHP files.
- For ORM or lifecycle changes, run the relevant lifecycle or ORM tests when available.
- For auth/access changes, run available auth/access tests or targeted smoke tests.
- For public entry point or CLI changes, run a minimal command that exercises the changed entry point when safe.
- If a framework change may affect packages, run a representative package consistency command when the environment allows it.

In PowerShell, execute eQual commands with `php run.php ...` from the project root, even when documentation examples use `./equal.run`.
