# Validation checklist — Update translations

## Content Validation

- [ ] All supported language files impacted by the task were updated.
- [ ] Translation file paths follow the expected entity/package namespace structure.
- [ ] Required root keys and intermediate objects are present.
- [ ] Every impacted field has `label`, `description`, and `help` when required.
- [ ] Added/updated selection values are translated consistently across languages.
- [ ] Impacted view names/titles are translated.
- [ ] Impacted view descriptions are present when required by conventions.
- [ ] Section identifiers used by impacted views are translated.
- [ ] Referenced actions/data providers/routes/exports include required translations.
- [ ] Business errors/messages introduced or modified are translated.
- [ ] Obsolete translations were removed after field/view/action rename or deletion.
- [ ] JSON syntax is valid in every touched translation file.

## JSON Schema Validation

Validate each modified translation file using the appropriate schema:

- [ ] **Model translations** (`i18n/{lang}/{Entity}.json`): Use schema `urn:equal:json-schema:core:model-translations`
- [ ] **Menu translations** (`i18n/{lang}/menu.*.*.json`): Use schema `urn:equal:json-schema:core:menu-translations`

**Validation procedure for each file**:
- Run `php run.php --get=core_json-validate` with:
  - `--json` parameter: the complete translation file content as JSON
  - `--schema_id` parameter: the appropriate schema ID from above
  - Confirm: no validation errors returned
  - See `AGENTS/00-general/VALIDATION-SCHEMAS.md` for detailed procedures

**Note**: Execute validation for each language file independently to ensure consistency.
