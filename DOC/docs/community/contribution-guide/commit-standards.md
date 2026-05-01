## Commit Convention

As commit convention, we adopt Conventional Commits v1.0.0. We have a history of commits that do not assume the convention, but any new commit should follow it.  
The commit message should be structured as follows:

```
<type>[(scope)]: <description>

[body]

[footer(s)]
```
(items within square brackets are optional)  
[See examples here](https://www.conventionalcommits.org/en/v1.0.0/#examples)

### Key Elements:

* **Type**: 
  - `fix:` for patching a bug
  - `feat:` for introducing a new feature
  - Other common types: `build:`, `docs:`, `style:`, `refactor:`, `test:`
  - Appending `!` after the type means a breaking change.
  
* **Description**: 
All commit messages should describe the action performed by the author of the commit (what has been done : "added", "removed", "improved", "corrected").
Example: `feat: allowed config object to extend other configs`

### Examples:
```
feat: allowed config object to extend other configs
```
```
feat(api)!: added sending of an email to the customer when a product is shipped
```
```
docs: improved comments
```
```
docs: corrected spelling in CHANGELOG
```

---