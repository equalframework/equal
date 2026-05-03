# Validation checklist — Update field

- [ ] The field exists (or is intentionally introduced/removed) in the intended entity.
- [ ] The field type and options are valid for the business intent.
- [ ] Relation updates point to existing entities/fields and preserve consistency.
- [ ] Computed/derived field dependencies were updated and remain complete.
- [ ] All impacted view references (form/list/search/filter) are valid.
- [ ] Form views were updated when the field affects data entry.
- [ ] List views were updated when the field affects table visibility or sorting.
- [ ] Related filters, actions, data providers, exports, and routes were reviewed.
- [ ] Translation entries were updated in every supported language.
- [ ] Impacted field translations include `label`, `description`, and `help`.
- [ ] Removed/renamed fields no longer have stale references in code, views, or i18n.
- [ ] No unrelated field changes were introduced in the same entity/package.
