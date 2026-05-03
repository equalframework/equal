# Validation checklist — Create data provider

- [ ] The provider file is located in the correct `data/` path.
- [ ] The linked entity/business object exists and is correctly referenced.
- [ ] Input parameters are explicitly validated (presence, type, constraints).
- [ ] Query/filter/scope logic is deterministic and aligned with business rules.
- [ ] Returned data structure is explicit and consistent with consumer expectations.
- [ ] Error cases are explicit and use package-convention error structures.
- [ ] Provider-related messages/errors are translated when user-visible.
- [ ] Any view/filter/action using this provider references it correctly.
- [ ] Provider label/description translations are present where required.
- [ ] No duplicate retrieval logic was added when reusable providers exist.
- [ ] No unrelated actions or other data providers were modified.
