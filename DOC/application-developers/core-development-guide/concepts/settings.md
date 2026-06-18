# Settings

eQual provides a centralized settings system for runtime configuration values. Settings are stored as model data, can be edited through the Settings application, and can be resolved dynamically by package code.

A setting is uniquely identified by:

* `package`: the package or domain that owns the setting.
* `section`: the shared configuration section.
* `code`: the hierarchical path inside the section.

The full logical name follows this structure:

```text
<package>.<section>.<thematic_area>[.<setting>[.<sub_setting>[.<item>]]]
```

Settings are usually accessed by passing the package, section, and code separately:

```php
Setting::get_value($package, $section, $code, $default);
```

Examples:

```php
Setting::get_value('core', 'locale', 'date.format', 'd/m/Y');
Setting::get_value('sale', 'features', 'quote.validity_delay', 30);
```

## Setting Object

Each `Setting` defines the metadata of a configurable parameter:

```json
{
  "id": 5,
  "code": "number.decimal_precision",
  "title": "Number of decimal digits",
  "package": "core",
  "form_control": "select",
  "section_id": 1,
  "description": "Number of decimal digits",
  "help": "Number of decimal digits to store for fields of type 'float'.",
  "type": "integer"
}
```

The actual runtime value is stored separately:

* `SettingValue` holds the value for a setting. Values can be scoped to a context using a selector such as `user_id` or `organization_id`.
* `SettingSequence` manages numeric counters. Sequences can also be scoped with selectors, for example to maintain independent numbering per organization.

Values and sequences use the same naming structure:

```text
value::<package>.<section>.<code>
sequence::<package>.<section>.<code>
```

Examples:

```text
value::purchase.accounting.invoice.sequence_format
sequence::purchase.accounting.invoice
```

## Package

The package identifies the eQual package, module, or application area responsible for the setting.

Examples:

```text
core
sale
documents
purchase
finance
stats
hr
identity
realestate
support
```

The package should represent the functional or technical domain that owns the setting.

## Section

Sections are shared high-level configuration groups. They describe the nature of the setting, not the business object itself.

Examples:

```text
core.locale.date.format
sale.features.quote.validity_delay
finance.accounting.accounting_entry.sequence.2024.Q1.SAL
```

Section names should remain stable and consistent across packages.

| Section | Functional name | Main usage |
| --- | --- | --- |
| `locale` | Localization and regional conventions | Languages, date/time formats, number formats, currencies, default units of measure |
| `security` | Security and access control | Authentication, MFA, roles, sessions, audit logs, access rules |
| `default` | Default values | Default values injected into fields, especially with `defaultFromSetting` |
| `accounting` | Accounting, fiscal and numbering logic | Accounting accounts, VAT, invoicing, document numbering, financial sequences |
| `analytics` | Reporting and analytics | Logs, metrics, KPIs, reporting configuration |
| `features` | Functional options and customization | Feature flags, configurable behavior, UI labels, templates, delays, business options |
| `storage` | Storage and data persistence | File paths, quotas, storage backends, persistence settings |
| `integration` | Integrations and connectors | APIs, tokens, endpoints, webhooks, external services |
| `system` | Technical and maintenance settings | Debug mode, internal notifications, versions, technical behavior |
| `workflow` | Business logic and processes | Statuses, transitions, automation rules, process behavior |
| `schedule` | Scheduling and time configuration | Calendars, working hours, cron jobs, planning rules |
| `organization` | Structure and organization | Internal organization setup not directly carried by an entity, SKUs, departments, fiscal or HR periods |

## Code

The code starts with a thematic area, then narrows the meaning progressively through more specific segments.

Examples:

```text
date
time
number
currency
unit
auth
booking
quote
invoice
sku
```

The thematic area should be broad enough to allow future extension. Prefer:

```text
core.locale.currency.symbol
core.locale.currency.symbol_position
core.locale.currency.decimal_precision
```

over isolated flat keys:

```text
core.locale.currency_symbol
core.locale.currency_position
```

Use dots for hierarchy and underscores inside a segment when a concept is composed of multiple words:

```text
decimal_separator
symbol_position
validity_delay
archive_delay
default_value
```

## Locale Settings

The `locale` section contains settings related to localization, regional conventions, and display formats.

Recommended structure:

```text
core.locale.date.*
core.locale.time.*
core.locale.number.*
core.locale.currency.*
core.locale.unit.*
```

### Date and Time

```json
{
  "core.locale.date.format": "d/m/Y",
  "core.locale.time.format": "H:i"
}
```

```php
Setting::get_value('core', 'locale', 'date.format', 'd/m/Y');
Setting::get_value('core', 'locale', 'time.format', 'H:i');
```

### Number Formatting

```json
{
  "core.locale.number.thousands_separator": ".",
  "core.locale.number.decimal_separator": ",",
  "core.locale.number.decimal_precision": 2
}
```

```php
Setting::get_value('core', 'locale', 'number.thousands_separator', '.');
Setting::get_value('core', 'locale', 'number.decimal_separator', ',');
Setting::get_value('core', 'locale', 'number.decimal_precision', 2);
```

The segment `number` is singular for consistency with `date`, `time`, `currency`, and `unit`.

### Currency

Currency settings should distinguish the currency code from its display symbol.

```json
{
  "core.locale.currency.code": "EUR",
  "core.locale.currency.symbol": "€",
  "core.locale.currency.symbol_position": "after",
  "core.locale.currency.decimal_precision": 2
}
```

```php
Setting::get_value('core', 'locale', 'currency.code', 'EUR');
Setting::get_value('core', 'locale', 'currency.symbol', '€');
Setting::get_value('core', 'locale', 'currency.symbol_position', 'after');
Setting::get_value('core', 'locale', 'currency.decimal_precision', 2);
```

The currency code should use an ISO-style code such as `EUR`, `USD`, `CHF`, or `GBP`. The symbol is only a display value, and can be ambiguous across currencies.

Keep `currency.decimal_precision` separate from `number.decimal_precision`:

```json
{
  "core.locale.number.decimal_precision": 3,
  "core.locale.currency.decimal_precision": 2
}
```

The first setting applies to generic numbers. The second applies to monetary values.

### Units of Measure

Default units of measure are stored under `core.locale.unit.*`.

```json
{
  "core.locale.unit.length": "m",
  "core.locale.unit.weight": "kg",
  "core.locale.unit.volume": "m3",
  "core.locale.unit.surface": "m2"
}
```

```php
Setting::get_value('core', 'locale', 'unit.length', 'm');
Setting::get_value('core', 'locale', 'unit.weight', 'kg');
Setting::get_value('core', 'locale', 'unit.volume', 'm3');
Setting::get_value('core', 'locale', 'unit.surface', 'm2');
```

For code, configuration, and exports, ASCII values are preferred:

```text
m2
m3
```

For user display, typographic labels may be used when needed:

```text
m²
m³
```

If the system needs to distinguish internal values from display labels, use a more explicit structure:

```json
{
  "core.locale.unit.surface.code": "m2",
  "core.locale.unit.surface.label": "m²",
  "core.locale.unit.volume.code": "m3",
  "core.locale.unit.volume.label": "m³"
}
```

## Choosing the Right Section

The section should describe the nature of the setting.

| Question | Recommended section |
| --- | --- |
| Is it related to formats, languages, currencies, units, timezones, or regional conventions? | `locale` |
| Is it related to authentication, access control, MFA, sessions, or audit logs? | `security` |
| Is it a default value injected into a field? | `default` |
| Is it related to accounting accounts, VAT, invoicing, fiscal logic, or numbering? | `accounting` |
| Is it related to reports, KPIs, metrics, or statistics? | `analytics` |
| Is it a configurable behavior, feature flag, label, template, business option, or delay? | `features` |
| Is it related to files, paths, quotas, or storage backends? | `storage` |
| Is it related to an API, webhook, connector, or external service? | `integration` |
| Is it a technical, internal, or maintenance setting? | `system` |
| Is it related to statuses, transitions, or business process rules? | `workflow` |
| Is it related to calendars, opening hours, working hours, or scheduled jobs? | `schedule` |
| Is it related to internal structure, SKUs, departments, or organization-level references? | `organization` |

## Distinguishing Common Sections

Use `default` when the setting directly provides the default value of a field:

```text
sale.default.booking.sojourn.age_range
```

This section is used by mechanisms such as `defaultFromSetting`.

Use `features` when the setting controls configurable behavior, optional features, delays, labels, templates, or functional customization:

```text
sale.features.quote.validity_delay
sale.features.option.validity_delay
sale.features.booking.archive_delay
```

Use `organization` when the setting describes an organizational reference, structural configuration, or internal mapping that does not fit directly within a dedicated entity:

```text
sale.organization.sku.downpayment.1
sale.organization.sku.downpayment.2
sale.organization.sku.bed_linens
sale.organization.sku.transport
sale.organization.sku.make_beds
```

## Naming Guidelines

| Principle | Rule |
| --- | --- |
| Hierarchy | Start from general to specific |
| Consistency | Use dots for hierarchy and underscores inside a segment |
| Clarity | Avoid abbreviations unless they are standard or obvious |
| Stability | Use shared sections consistently across packages |
| Explicitness | Prefer `currency.code` and `currency.symbol` over a generic `currency` value |
| Future extension | Reserve thematic areas for further extension, such as `auth.passkey.*` |
| Avoid redundancy | Do not repeat the same concept unnecessarily in multiple path segments |

Boolean settings should be grouped by functional area and named according to the behavior they activate.

Recommended:

```text
core.security.auth.passkey.enabled
sale.features.booking.auto_archive
documents.features.ocr.enabled
```

Avoid vague names such as:

```text
enabled
active
use_feature
```

unless the parent path is sufficiently explicit, as in:

```text
core.security.auth.passkey.enabled
```

## Examples

Locale formatting:

```php
Setting::get_value('core', 'locale', 'date.format', 'd/m/Y');
Setting::get_value('core', 'locale', 'time.format', 'H:i');

Setting::get_value('core', 'locale', 'number.thousands_separator', '.');
Setting::get_value('core', 'locale', 'number.decimal_separator', ',');
Setting::get_value('core', 'locale', 'number.decimal_precision', 2);
```

Currency settings:

```php
Setting::get_value('core', 'locale', 'currency.code', 'EUR');
Setting::get_value('core', 'locale', 'currency.symbol', '€');
Setting::get_value('core', 'locale', 'currency.symbol_position', 'after');
Setting::get_value('core', 'locale', 'currency.decimal_precision', 2);
```

Units of measure:

```php
Setting::get_value('core', 'locale', 'unit.length', 'm');
Setting::get_value('core', 'locale', 'unit.weight', 'kg');
Setting::get_value('core', 'locale', 'unit.volume', 'm3');
Setting::get_value('core', 'locale', 'unit.surface', 'm2');
```

Passkey authentication:

```php
Setting::get_value('core', 'security', 'auth.passkey.rp.id', 'example.com');
Setting::get_value('core', 'security', 'auth.passkey.user_verification', 'preferred');
Setting::get_value('core', 'security', 'auth.passkey.authenticator_support.usb', true);
```

Accounting sequences:

```php
Setting::assert_sequence('finance', 'accounting', 'accounting_entry.sequence.2024.Q1.SAL');
```

Sale settings:

```php
Setting::get_value('sale', 'features', 'quote.validity_delay', 30);
Setting::get_value('sale', 'features', 'option.validity_delay', 15);
Setting::get_value('sale', 'features', 'booking.archive_delay', 365);

Setting::get_value('sale', 'organization', 'sku.downpayment.1');
Setting::get_value('sale', 'organization', 'sku.bed_linens');
Setting::get_value('sale', 'organization', 'sku.transport');
Setting::get_value('sale', 'organization', 'sku.make_beds');
```
