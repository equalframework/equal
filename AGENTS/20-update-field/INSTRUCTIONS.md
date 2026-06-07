1. Locate the entity class.
2. Inspect nearby fields for conventions.
3. Add or update the field definition.
4. Check field type, usage, default, required, readonly, relations, and dependents.
5. If renaming or removing a field, search for all references.
6. Update impacted form views.
7. Update impacted list views.
8. Update filters, routes, actions, exports, dashboards, or data providers when relevant.
9. Update translations in every supported language.
10. Remove obsolete references if a field is renamed or deleted.
11. Avoid changing unrelated fields.
12. If the entity class was modified, run `./equal.run --do=test_db-access`; if the configured database does not exist, ensure `config/config.json` exists and is valid, then run `./equal.run --do=init_db`.
13. If the entity class was modified, reinitialize the impacted package with `./equal.run --do=init_package --package={package} --force=true`.
14. Refer to `AGENTS/AGENTS_REFERENCE.md` for detailed field conventions.
