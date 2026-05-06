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

## JSON Schema Validation

Validate the action handler definition using the controller action schema:

- [ ] **Action schema validation**: Use schema `urn:equal:json-schema:core:controller.action`

**Validation procedure**:
1. Extract the `eQual::announce()` metadata array from the PHP file through `php run.php --do={package}_{path}_{action} --announce=true` to get the JSON representation of the action handler definition.
2. Convert the metadata to JSON representation
3. Run `php run.php --get=core_json-validate` with:
   - `--json` parameter: the metadata as JSON string
   - `--schema_id` parameter: `urn:equal:json-schema:core:controller.action`
   - Confirm: no validation errors returned
4. See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for detailed procedures

**Required fields in metadata**:
- `type`: "get" or "do" indicating retriever vs action
- `name`: Name of the action handler
- `package_name`: Package the action belongs to
- `description`: Clear description of what the action does
