1. Locate the entity class.
2. Inspect nearby fields for conventions.
3. Add or update the field definition.
4. Check field type, usage, default, required, readonly, relations, and dependents.
5. When field hooks need synchronization logic, expose the behavior as a named ORM action in `getActions()` and trigger it with `$self->do('action_name')` instead of calling a private helper directly.
6. Avoid adding public helper methods unless an existing consumer or stable cross-entity API clearly requires them.
7. If renaming or removing a field, search for all references.
8. Update impacted form views.
9. Update impacted list views.
10. Update filters, routes, actions, exports, dashboards, or data providers when relevant.
11. Update translations in every supported language.
12. Remove obsolete references if a field is renamed or deleted.
13. Avoid changing unrelated fields.
14. If the entity class was modified, run `./equal.run --do=test_db-access`; if the configured database does not exist, ensure `config/config.json` exists and is valid, then run `./equal.run --do=init_db`.
15. If the entity class was modified, reinitialize the impacted package with `./equal.run --do=init_package --package={package} --force=true`.
16. Refer to `AGENTS/AGENTS_REFERENCE.md` for detailed field conventions.
