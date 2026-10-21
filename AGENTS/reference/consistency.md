# Guidelines for resolving package consistency warnings and errors

These guidelines explain how to handle warnings and errors reported by the package consistency checker.

## Action

### `core_test_package-consistency`

Parameters:

- `package`: package to validate.
- `level`: optional notification level (`*`, `warn`, or `error`).
- `strict`: optional strict-mode flag.
- `exit_on_error`: optional flag controlling the command exit code when errors are found.

Before applying a fix, read the entity class and the views that use the affected field. Use the field type, selection values, relations, computed behavior, visibility conditions, defaults, and validations to describe actual behavior. Do not infer behavior from the field name alone.

## Fixes

### Warnings

#### I18 - Value for property `description` should not be empty for field `xxx`

Create a concise description of what the field represents.

Guidelines:

- Write one short, declarative sentence.
- State the field's business meaning rather than telling the user what to do.
- Keep it understandable outside the form where the field appears.
- Mention whether the value is calculated or inherited when that fact defines the field.
- Do not repeat the label without adding meaning.

Example:

```json
"description": "Taux de TVA appliqué à la ligne."
```

#### I18 - Value for property `help` should not be empty for field `xxx`

Create help text that adds practical information beyond the description.

Guidelines:

- Write one or two medium-length sentences.
- For editable fields, explain what to enter or select and when it is useful.
- For selections, explain the meaning or effect of the available choices.
- For conditional fields, state when the field is visible or applicable.
- For calculated, inherited, or synchronized fields, explain how the value is populated.
- Include relevant constraints, dependencies, defaults, or side effects.
- Use an imperative form for user input, such as `Sélectionnez` or `Indiquez`.
- Use a factual form for automatic behavior, such as `Cette valeur est calculée automatiquement`.
- Do not simply rephrase the description and do not invent behavior that is absent from the class or views.

Example:

```json
"help": "Le taux est normalement déterminé à partir du prix sélectionné."
```

#### ORM - Value for attribute 'xxx' should start with uppercase for field 'xxx' referenced in file packages/xxx/classes/xxx.class.php

Modify the first character to uppercase.

Guidelines:

- First character to uppercase, if it does not change the meaning.
- Keep same meaning, if for example `eIDAS signature level (ses = drawn)` then modify to `The eIDAS signature level (ses = drawn)` to not modify the `eIDAS` name writing.

#### ORM - Value for attribute 'description' should not exceed 65 chars for field 'origin_object_id' referenced in file packages/finance/classes/accounting/AccountingEntry.class.php

Reduce the description length, make it concise. Use the help field for more information.

### Errors

#### ERROR - GUI - Class {entity}: missing default form view (/views/xxx\\xxx.{view_id}.json)

Generate the missing view by running `core_config_generate-view`

Params:
- entity: use the class from the error message
- view_id: use the view id from the error message

## French writing conventions

- Use clear, professional French with correct accents and punctuation.
- End descriptions and help text with a period.
- Prefer the terminology already used by the entity, its views, and neighboring translations.
- Keep technical identifiers out of user-facing text unless users need them.

## Validation

After updating translations:

1. Validate the JSON syntax of every modified file.
2. Run `core_test_translation-consistency` for every affected entity and language.
3. Rerun `core_test_package-consistency` for the package.
4. Confirm that the targeted warnings are gone and report unrelated errors separately.
