# Validation checklist — Create action handler

## Structure Validation

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

## Inline ORM Action Validation

- [ ] Every foreseeable business precondition is checked by a declared policy.
- [ ] Policies cover the current state, required values, required relations, related-object compatibility, and all targeted objects.
- [ ] Policies do not create, update, or delete business objects.
- [ ] The complete collection is validated before the first mutation.
- [ ] The `do*` method contains only the expected success path.
- [ ] The `do*` method does not perform functional rejection already belonging to a policy.
- [ ] The `do*` method contains no `try/catch`.
- [ ] The `do*` method does not start, commit, roll back, or otherwise manage an SQL transaction.
- [ ] Predictable failures use stable policy error keys.

## Announcement Metadata Validation

Validate the action handler definition through eQual's `announce` mechanism. No dedicated action-consistency controller currently covers action announcement metadata, so this is an allowed `core_json-validate` exception.

- [ ] **Action schema validation**: Use schema `urn:equal:json-schema:core:controller.action`

**Validation procedure**:
1. Build the controller name from the file path: `packages/{package}/actions/{path}/{action}.php` becomes `{package}_{path_with_underscores}_{action}`; omit the path segment when the file is directly under `actions/`.
2. Do not parse the PHP action file manually.
3. Ask eQual for the `eQual::announce()` metadata through `./equal.run --do={controller} --announce=true`; this returns the action contract without executing the business logic after `eQual::announce()`.
4. Use the returned `announcement` metadata as JSON representation.
5. Validate the returned metadata with `core_json-validate`:
   - `--json` parameter: the metadata as JSON string
   - `--schema_id` parameter: `urn:equal:json-schema:core:controller.action`
   - Confirm: no validation errors returned
   - Avoid embedding the JSON representation directly in a shell command; see `AGENTS/00-general/POWERSHELL.md` for PowerShell-safe handling
6. See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for detailed procedures

**Required fields in metadata**:
- `type`: "get" or "do" indicating retriever vs action
- `name`: Name of the action handler
- `package_name`: Package the action belongs to
- `description`: Clear description of what the action does
