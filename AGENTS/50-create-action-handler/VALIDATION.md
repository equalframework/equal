# Validation checklist — Create action handler

- [ ] The handler file is located in the correct `actions/` path.
- [ ] The linked entity/business object exists and is correctly referenced.
- [ ] Input parameters are explicitly validated (presence, type, constraints).
- [ ] Authorization/security expectations are respected for the action context.
- [ ] Return payload structure is explicit and consistent with package conventions.
- [ ] Business errors are explicit, deterministic, and mapped to clear conditions.
- [ ] Business errors/messages are translated in supported languages.
- [ ] Any view/button/menu reference to this action is valid.
- [ ] Action label/description translations are present where required.
- [ ] No duplicate logic was added when reusable services already exist.
- [ ] No unrelated action or data-provider files were modified.
