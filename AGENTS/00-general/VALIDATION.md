# Validation checklist — General (all tasks)

- [ ] All modified files are within the intended package scope, or within the identified framework subsystem for framework internals tasks.
- [ ] The `packages/core` package was not modified unless explicitly requested.
- [ ] No unrelated files were changed.
- [ ] Naming conventions match nearby package files.
- [ ] Namespaces and paths are consistent.
- [ ] PHP syntax is valid for all touched PHP files (see validation procedures).
- [ ] JSON syntax is valid for all touched JSON files (see validation procedures).
- [ ] If any `classes/*.class.php` file was modified, `./equal.run --do=test_db-access` was run, or the environment limitation was recorded.
- [ ] If any `classes/*.class.php` file was modified and the configured database was missing, `config/config.json` was confirmed valid and `./equal.run --do=init_db` was run.
- [ ] If any `classes/*.class.php` file was modified, the impacted package was reinitialized with `./equal.run --do=init_package --package={package} --force=true`.
- [ ] Existing package or framework subsystem conventions were followed.
- [ ] No obsolete or duplicate logic was introduced.
- [ ] The change is understandable and reviewable by a human developer.
- [ ] The final response reports changed files, validation commands actually run, and skipped validation with reasons.
