# Validation checklist - Framework internals

## Scope

- [ ] The changed subsystem was identified.
- [ ] The change is either framework-only or the mixed framework/package impact is explicitly reported.
- [ ] `packages/core` production code was not modified unless explicitly requested.
- [ ] Public framework APIs kept compatible signatures and return contracts, or the intentional breaking change was requested.

## References

- [ ] Callers and overrides of changed methods/classes were searched.
- [ ] Nearby framework conventions were followed.
- [ ] Package-specific assumptions were not introduced into framework code.
- [ ] Error keys, exception types, HTTP statuses, and response shapes remain compatible unless intentionally changed.

## Syntax and Tests

- [ ] `php -l` was run for every touched PHP file.
- [ ] Relevant ORM/lifecycle/auth/access/public-entry tests or smoke checks were run where available.
- [ ] Any skipped validation is reported with the exact reason.

## Risk Review

- [ ] No unrelated framework refactor was introduced.
- [ ] No unnecessary global state, static cache, or side effect was added.
- [ ] The final diff is small enough for human review.
