# Validation checklist — Create data provider

## Structure Validation

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

## JSON Schema Validation

Validate the data provider definition using the controller action schema:

- [ ] **Data provider schema validation**: Use schema `urn:equal:json-schema:core:controller.action`

**Validation procedure**:
1. Build the controller name from the file path: `packages/{package}/data/{path}/{provider}.php` becomes `{package}_{path_with_underscores}_{provider}`; omit the path segment when the file is directly under `data/`.
2. Extract the `eQual::announce()` metadata through `./equal.run --get={controller} --announce=true`; this returns the provider contract without executing the retrieval logic after `eQual::announce()`.
3. Use the returned `announcement` metadata as JSON representation.
4. Run `./equal.run --get=core_json-validate` with:
   - `--json` parameter: the metadata as JSON string
   - `--schema_id` parameter: `urn:equal:json-schema:core:controller.action`
   - Confirm: no validation errors returned
5. See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for detailed procedures

**Required fields in metadata**:
- `type`: "get" indicating retriever/data provider
- `name`: Name of the data provider
- `package_name`: Package the provider belongs to
- `description`: Clear description of what data is returned
