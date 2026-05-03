1. Confirm that the change belongs in `data/` and is a read/query endpoint, not an `actions/` handler and not an inline model action.
2. Check whether an existing provider can be extended, filtered, or reused before creating a new one.
3. Inspect similar providers in the same package and follow their naming, announcement, access, and response patterns.
4. Locate the linked entity class and understand which fields, relations, computed fields, and access constraints the provider depends on.
5. Define the provider as a query contract: what it reads, for whom, and in which format.
6. Write a precise `description` in `eQual::announce()` so the provider is self-documenting in the workbench.
7. Declare all expected `params` explicitly, with proper types, `required`, `default`, `selection`, `foreign_object`, and visibility rules when relevant.
8. Reuse `extends => 'core_model_collect'` or another base provider when the endpoint is mainly a specialized filter on top of an existing generic query.
9. Request only the providers you actually need, typically `context`, and add `orm`, `auth`, `adapt`, or others only when the logic requires them.
10. Define the `access` block deliberately: `public`, `protected`, or `private`, depending on the data exposure level.
11. Define the `response` contract explicitly, including `content-type`, `charset`, and `accept-origin`; use the real output type, not a default placeholder.
12. Decide early whether the provider returns JSON data, a binary stream, or a relayed payload from another provider.
13. Read only the fields that are necessary for the output and for any access or business checks.
14. Keep the provider focused on retrieval logic; avoid embedding write-side business workflows in `data/`.
15. If the provider is a composition layer, prefer orchestrating existing model logic or other providers with `eQual::run('get', ...)` instead of duplicating query behavior.
16. Normalize or prune incompatible filters before building the final domain, especially when some parameters are conditionally hidden or mutually exclusive.
17. Validate inputs as early as possible and fail with explicit exception keys such as `missing_id`, `unknown_mailbox`, or `document_unknown`.
18. Use stable business error keys, not only free-text messages, so callers can handle failures predictably.
19. Reserve `trigger_error("APP::...")` for diagnostics and operational warnings; use exceptions for functional errors that must stop the request.
20. Ensure business errors are translatable wherever they are surfaced to users.
21. Adapt output only when needed, for example with `->adapt('json')`, and keep the final payload shape consistent across success cases.
22. Build the HTTP response explicitly with `httpResponse()`: body, headers, status, and binary mode when needed.
23. Link the provider to views only when the UI genuinely needs a custom `get=` endpoint beyond standard model reads and collects.
24. Update translations only for user-facing labels, descriptions, route labels, or view texts related to that provider; do not treat data providers like form actions by default.
25. Do not reference `AGENTS_REFERENCE.md` here; in this repository the relevant references are `AGENTS.md` and the framework docs for controllers and data providers.