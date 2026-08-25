# Settings and Classification

eQual provides a centralized settings system for runtime configuration values. A setting has two distinct parts:

* a `Setting` record describes and classifies the parameter;
* a `SettingValue` or `SettingSequence` record stores its runtime value.

Settings are displayed in the Settings application and can be resolved dynamically by package code.

## Classification Key

A setting is classified by three values:

| Component | Role |
| --- | --- |
| `package` | Identifies the package or functional domain that owns the setting. |
| `section` | Selects a shared, global category from the `SettingSection` catalog. |
| `code` | Identifies the parameter inside that package and section. Dots may express a hierarchy. |

Together they form the logical setting name:

```text
<package>.<section>.<code>
```

For example:

```text
core.locale.date.format
core.security.auth.passkey.enabled
core.system.time.encoding
```

At database level, uniqueness is enforced on `package`, `section_id`, and `code`. The readable `section` field is computed from `section_id`, and the full `name` is computed from the three classification components. Consequently:

* use a section **code** in definitions and API calls;
* never hard-code a section database ID;
* do not include the package name or section code again in `code`.

The dots inside `code` are a naming convention. They make related settings easier to browse but do not create additional database entities.

## Global Section Catalog

Sections are global categories shared by all packages. Their source catalog is:

```text
packages/core/init/data/scripts/setting-sections.json
```

The catalog currently defines these sections:

| Section | Use for |
| --- | --- |
| `locale` | Regional conventions, languages, date and time formats, number formats, currencies, time zones, and units. |
| `main` | Legacy general settings. This section is deprecated and must not receive new settings. |
| `security` | Authentication, access control, MFA, sessions, audit, and other security policy. |
| `default` | Values that are injected as defaults, including values consumed by `defaultFromSetting`. |
| `analytics` | Reporting, metrics, KPIs, statistics, and observability configuration. |
| `features` | Feature flags, optional behavior, functional customization, UI options, labels, templates, and delays. |
| `storage` | Files, paths, quotas, persistence, and storage backends. |
| `integration` | APIs, tokens, endpoints, webhooks, connectors, and external services. |
| `system` | Technical runtime behavior, internal configuration, maintenance, and compatibility options. |
| `workflow` | Statuses, transitions, automation, and business process rules. |
| `schedule` | Calendars, working hours, opening hours, cron jobs, and planning rules. |

The catalog is broader than the settings initially supplied by `core`. At present, the core setting definitions use only `locale`, `main`, `security`, and `system`; the other catalog entries are available for settings owned by other packages.

!!! warning "Catalog entries and suggested names are not the same thing"
    A section can be used by the declarative initializer only after its code exists in the catalog. For example, the deprecated `main` description mentions `organization` as its intended replacement, but `organization` is not currently seeded. A definition using it is therefore rejected until that section is explicitly added to the catalog.

### Choosing a Section

The package identifies the business or technical domain; the section identifies the **nature of the configuration**. Do not create a section merely to repeat the package or an entity name.

| Question | Recommended section |
| --- | --- |
| Does it control regional representation or units? | `locale` |
| Does it control authentication, authorization, or security policy? | `security` |
| Is the value directly used as a field default? | `default` |
| Does it enable or customize optional functional behavior? | `features` |
| Does it control a status, transition, or automated process? | `workflow` |
| Does it control a calendar, time window, or scheduled execution? | `schedule` |
| Does it configure reporting or metrics? | `analytics` |
| Does it configure file persistence or quotas? | `storage` |
| Does it configure an external system or endpoint? | `integration` |
| Is it technical, internal, or maintenance-oriented? | `system` |

When several sections seem plausible, classify by how the value is consumed. For example, a finance package can use `default` for a default account, `workflow` for an approval rule, and `integration` for an accounting API endpoint. A dedicated `finance` or `accounting` section is not required because ownership is already carried by `package`.

## Declarative Initialization

The core reference implementation is located in:

```text
packages/core/init/data/scripts/
├── 10-create-setting-sections.php
├── 20-create-settings.php
├── setting-sections.json
└── settings.json
```

Package initialization scans PHP files in lexical order, so the numeric prefixes are significant. The classification lifecycle is:

1. `10-create-setting-sections.php` reads the section catalog and creates every `SettingSection`.
2. It creates the top-level fields in English, then applies the entries in `translations` using their language codes.
3. `20-create-settings.php` reads all persisted sections and builds a `code => id` map.
4. For each setting definition, it resolves `section` through that map, removes the textual `section`, and assigns the generated `section_id`.
5. It creates the `Setting` and then its translations, choices, initial value, and sequences.

Both JSON files must contain a top-level JSON array, and every item must be an object. An invalid JSON document, an invalid list shape, or an unknown section stops initialization. In particular, a setting cannot silently introduce a new section through this declarative path.

### Section Definition

A section definition contains stable, language-neutral `code` and English display fields at the top level:

```json
{
  "code": "features",
  "name": "Features",
  "description": "Customization & UI",
  "translations": {
    "fr": {
      "name": "Fonctionnalités",
      "description": "Personnalisation, UI et libellés"
    }
  }
}
```

Do not provide `id`. Database identifiers are generated and settings are linked by resolving the section code.

### Setting Definition

The classification fields are supplied together with metadata and optional related records:

```json
{
  "package": "core",
  "section": "locale",
  "code": "date.format",
  "title": "Date format",
  "form_control": "select",
  "description": "Format used to display dates.",
  "help": "PHP date format used to display dates.",
  "type": "string",
  "translations": {
    "fr": {
      "title": "Format de date",
      "description": "Format utilisé pour l'affichage des dates.",
      "help": "Format de date PHP utilisé pour l'affichage des dates."
    }
  },
  "choices": [
    { "name": "d/m/Y", "value": "d/m/Y" },
    { "name": "Y-m-d", "value": "Y-m-d" }
  ],
  "value": "d/m/Y"
}
```

The loader treats some keys specially:

| Key | Loader behavior |
| --- | --- |
| `section` | Resolved to `section_id`; it is not copied directly into the `Setting` create payload. |
| `translations` | Applied to the setting after its English record is created. |
| `choices` | Each item creates a `SettingChoice`; nested translations are applied afterward. |
| `value` | Creates one unscoped `SettingValue`. No language is forced, so non-multilingual values use `DEFAULT_LANG`. |
| `sequences` | Each item creates a `SettingSequence` linked to the setting. |

All other supported fields are passed to `Setting::create()`. Common metadata includes `title`, `description`, `help`, `type`, `form_control`, `is_multilang`, `object_class`, `is_sequence`, and `is_deprecated`.

The loader paths above are explicitly tied to the core files. A `settings.json` placed in another package is not discovered automatically. A package that owns additional settings must create them from its own initialization script or another supported creation path, while reusing a declared global section code.

## Declarative Catalog vs Runtime Creation

The main supported creation paths deliberately have different strictness:

* The declarative loader rejects an unknown section.
* The `core_config_create-setting` action also requires an existing package and section.
* The lower-level `Setting::assert_value()` and `Setting::assert_sequence()` helpers can create a missing section on demand, but the model explicitly discourages this fallback.

On-demand section creation should not be used to define taxonomy. Such a record lacks the catalog's curated English metadata and translations, and its accidental spelling becomes a global section code. Declare the section first, then create the setting.

## Values, Choices, and Sequences

Classification belongs to `Setting`; runtime data belongs to related records:

| Entity | Purpose | Relation or selector key |
| --- | --- | --- |
| `SettingValue` | Stores a regular value, optionally for one user. | `setting_id`, `user_id` |
| `SettingChoice` | Stores an allowed or suggested value and its translatable label. | Linked to one setting |
| `SettingSequence` | Stores a positive integer counter, optionally for one user. | `setting_id`, `user_id` |

An unscoped value has no `user_id`. The core model supports `user_id` as its selector key:

```php
$global = Setting::get_value('core', 'locale', 'date.format', 'd/m/Y');

$personal = Setting::get_value(
    'core',
    'locale',
    'date.format',
    'd/m/Y',
    ['user_id' => $user_id]
);
```

Do not document or pass selectors such as `organization_id` unless the model has been explicitly extended to support them.

Regular values and sequences retain the same logical name as their parent setting. There is no additional `value::` or `sequence::` prefix in their stored `name` field.

## Deprecation

Legacy settings can remain in the definitions with `is_deprecated: true` so existing installations and integrations can recognize them. Deprecated names are compatibility aliases, not naming examples for new code.

For new integrations:

* use only active setting definitions;
* do not add settings to the deprecated `main` section;
* keep the replacement and the legacy record distinct;
* migrate callers before eventually removing a legacy definition.

## Naming Guidelines

Start `code` with a thematic area and narrow its meaning from general to specific:

```text
date.format
number.decimal_separator
auth.passkey.enabled
booking.archive_delay
```

Use dots between hierarchy levels and underscores inside a multi-word segment:

```text
currency.symbol_position
quote.validity_delay
auth.totp.allowed_failed_attempts
```

Prefer stable, explicit names:

```text
core.locale.currency.code
core.locale.currency.symbol
core.locale.currency.decimal_precision
```

Avoid flat or ambiguous names when a hierarchy adds useful context:

```text
currency_symbol
enabled
active
```

Existing flat or irregular legacy codes should not be copied into new definitions. Mark them as deprecated when introducing a canonical replacement.

## Runtime API

Settings are normally read by passing the three classification components separately:

```php
Setting::get_value($package, $section, $code, $default, $selector, $lang);
```

Examples:

```php
Setting::get_value('core', 'locale', 'date.format', 'd/m/Y');
Setting::get_value('core', 'locale', 'number.decimal_precision', 2);
Setting::get_value('core', 'security', 'auth.passkey.enabled', false);
Setting::get_value('core', 'system', 'time.encoding', 'frontend');
```

Use the assertion helpers when application logic must guarantee that a value or sequence record exists:

```php
Setting::assert_value('sale', 'features', 'quote.validity_delay', 30);
Setting::assert_sequence('finance', 'workflow', 'invoice.sequence', 1);
```

The fallback passed to `get_value()` is returned when either the setting definition or the matching scoped value does not exist.

## Authoring Checklist

Before adding a setting:

1. Confirm that `package` identifies the owner.
2. Select an existing section by the nature of the setting.
3. If no section fits, extend the global catalog before referencing the new code.
4. Choose a hierarchical `code` that does not repeat package or section.
5. Provide English metadata and translations for supported languages.
6. Declare type, control, choices, initial value, or sequence data where applicable.
7. Never depend on a section database ID.
8. Treat deprecated definitions as migration aids only.
