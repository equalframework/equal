1. Code identifiers, variables, and comments must be written in English.
2. Agents must not merge pull requests.
3. Agents must not modify the `core` package.
4. Agents must work inside the active package only.
5. Agents must identify the impacted package before editing files.
6. Agents must not assume that changing a class is sufficient.
7. Agents must always check the impact on `classes/`, `views/`, `i18n/`, `actions/`, and `data/`.
8. Agents must preserve namespace and path consistency.
9. Agents must avoid inventing framework conventions.
10. Agents must prefer existing patterns already present in the package.
11. Agents must keep changes minimal and related to the requested task.
