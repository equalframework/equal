# Validation checklist — Create entity

- [ ] The new entity class was created at the expected ORM path for the target package.
- [ ] The namespace matches the file path and the class name matches the file name.
- [ ] Entity metadata/configuration follows package ORM conventions.
- [ ] Every declared field has a valid type and coherent options.
- [ ] Relation fields target existing entities and valid target fields.
- [ ] Computed/derived fields declare complete and valid dependencies.
- [ ] Required default form and/or list views were created when the entity is user-facing.
- [ ] Newly created views reference only existing fields.
- [ ] Translation files were created or updated for all supported languages.
- [ ] Field translations include `label`, `description`, and `help` where required.
- [ ] View names and section identifiers introduced by this entity are translated.
- [ ] No unrelated entity definition was modified.
