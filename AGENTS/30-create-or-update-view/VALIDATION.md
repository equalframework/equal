# Validation checklist — Create or update view

- [ ] The view file path and naming follow package conventions.
- [ ] JSON syntax is valid and the document structure matches expected schema.
- [ ] The referenced entity exists and matches the intended business object.
- [ ] Every referenced field exists in the corresponding ORM/entity class.
- [ ] Form layout structure (sections, groups, widgets) is valid and coherent.
- [ ] List layout structure (columns, sort/filter config) is valid and coherent.
- [ ] Referenced actions/data providers/routes/exports exist and are compatible.
- [ ] View labels/titles are translated in supported languages.
- [ ] Section identifiers used in the view are translated.
- [ ] No obsolete fields or stale action references remain in the updated view.
- [ ] No unrelated views were modified.
