## 📘 Overview

This guide is intended for LLM agents assisting developers working on software projects based on the eQual framework. It describes what the agents are allowed to do, how code should be modified or added, and the standards and conventions to follow to ensure safe, clean, and maintainable contributions.

------

## 📌 Agent Role and Responsibilities

- Agents act as assistants to human developers: they can propose and generate full code implementations, but human review is always required.
- Agents may create pull requests, but must not merge them.
- The goal is to accelerate development by performing repetitive or well-structured changes.

------

## 📦 Scope of Intervention

### Package boundaries

- Agents must operate within a single package.
- The `core` package is off-limits, even if present.

### Allowed Components (within the active package)

| Component         | Folder path    | File extension | Notes                            |
| ----------------- | -------------- | -------------- | -------------------------------- |
| Class definitions | `classes/`     | `.class.php`   | ORM entities inheriting `Model`  |
| Action handlers   | `actions/`     | `.php`         | Controller-like endpoints        |
| Data providers    | `data/`        | `.php`         | Structured data access functions |
| Views             | `views/`       | `.json`        | UI layout definitions            |
| Translations      | `i18n/{lang}/` | `.json`        | Per-entity translation files     |

Each of these folders is organized by entity namespace. For example:

- `classes/booking/Booking.class.php` → entity `sale\booking\Booking`
- `data/booking/Booking/**/*.php` → data providers for that entity
- `actions/booking/Booking/**/*.php` → action controllers
- `views/booking/Booking.*.json` → views for that entity
- `i18n/fr/booking/Booking.json` → translations for that entity in French

------

## 🧱 Entity Class Structure (ORM Model)

Each class:

- Inherits from `equal\orm\Model`
- Is located at `classes/{namespace}/Entity.class.php`
- Defines fields via `getColumns()`

### Field Types

| Type      | Description                   |
| --------- | ----------------------------- |
| string    | Plain or formatted text       |
| integer   | Whole number                  |
| float     | Decimal number                |
| boolean   | True/false                    |
| date      | Date only                     |
| datetime  | Date and time                 |
| many2one  | Foreign key                   |
| one2many  | Reverse foreign key           |
| many2many | Many-to-many via join table   |
| computed  | Dynamically calculated field  |
| alias     | Field alias for another field |

### Field Properties

| Property                                            | Purpose                                             |
| --------------------------------------------------- | --------------------------------------------------- |
| `type`                                              | Mandatory, specifies field type                     |
| `usage`                                             | Refines format/rendering (e.g. `amount/money:2`)    |
| `default`                                           | Default value (static, method, closure, or setting) |
| `required`                                          | Enforces presence                                   |
| `readonly`                                          | Prevents user editing                               |
| `selection`                                         | Restricts possible values                           |
| `multilang`                                         | Enables i18n support                                |
| `onupdate`                                          | Method called on field change                       |
| `onrevert`                                          | Method called when value is cleared                 |
| `foreign_object`, `foreign_field`, `rel_table` etc. | Define relations                                    |
| `domain`                                            | Filters valid values for relation                   |
| `dependents`                                        | Triggers refresh of related computed fields         |

------

## 🧮 Computed Fields

| Property      | Description                                       |
| ------------- | ------------------------------------------------- |
| `type`        | Must be `computed`                                |
| `function`    | Method name or closure computing the value        |
| `result_type` | Output type (same list as normal field types)     |
| `store`       | If true, value is persisted to DB                 |
| `relation`    | Path to fetch related values via relations        |
| `instant`     | Force instant recomputing of the field when reset |

Avoid calling `update()` inside a computed field function.

------

## 🚀 Inline Actions on Entities

Defined via `getActions()` inside the entity class:

```php
public static function getActions() {
  return [
    'init' => [
      'description' => 'Initialize report lines.',
      'policies'    => [],
      'function'    => 'doInit'
    ]
  ];
}
```

Actions are triggered with:

```php
Report::create()->do('init');
```

Each action can define `policies` to restrict access.

------

## 🎨 Views (`views/*.json`)

Views define how data is presented to users.
 They are stored as JSON files and named as:

```
{EntityBasename}.{ViewType}.{ViewName}.json
```

E.g. `Booking.form.default.json`, `User.list.default.json`

Each view must declare a `layout`, and optionally: `name`, `description`, `domain`, `filters`, `routes`, `header`, `actions`, `controller`, `access`, etc.

------

## 🧾 Form View Structure

Form views include:

- `layout.groups[]` → horizontal blocks
- `sections[]` → tabs within groups
- `rows[]` → contain `columns[]` which hold `items[]`

Items:

```json
{
  "type": "field",
  "value": "name",
  "width": 50,
  "readonly": false,
  "widget": {
    "type": "string"
  }
}
```

Widgets for relational fields may also define `view`, `domain`, or `header.actions` for nested configuration.

------

## 📄 List View Structure

Support:

- Sorting: `order`, `sort`
- Pagination: `limit`
- Filters: `filters[]`
- Grouping: `group_by[]`
- Aggregates: `operations`
- Header actions: `header.actions`
- Bulk selection actions: `header.selection`
- Export buttons: `exports[]`
- Advanced search: `advanced_search`

Items define columns:

```json
{
  "type": "field",
  "value": "status",
  "sortable": true,
  "widget": {
    "type": "select",
    "values": ["draft", "confirmed"]
  }
}
```

------

## 🧷 Widgets and Display Customization

Widgets control rendering of a field.

Examples:

```json
{
  "widget": {
    "type": "date",
    "usage": "datetime/short",
    "text_color": "#333",
    "icon": "calendar",
    "icon_position": "left"
  }
}
```

Widget styles supported:

- `text_color`, `background_color`, `text_weight`, `border_color`, `icon`, `padding`, etc.

------

## 🌍 Translations (i18n)

### Multilingual Fields

To make a field translatable:

```php
'name' => [ 'type' => 'string', 'multilang' => true ]
```

### Updating translations

```php
MyClass::create($fields, 'fr');
MyClass::ids($id)->update($fields, 'en');
```

------

### Class Translation Files

Location:

```
packages/{package}/i18n/{lang}/{Class}.json
```

Includes:

- `name`, `plural`, `description`
- `model`: field labels, descriptions, selections, help
- `view`: form/list names, layout section labels

Example:

```json
"model": {
  "status": {
    "label": "Statut",
    "description": "Workflow state of the object.",
    "selection": { "draft": "Draft" },
    "help": ""
  }
}
```

------

### View Documentation (optional)

Place `.md` files in `i18n/` to document what a view does.
 E.g. `Booking.list.default.md`

---

## 🌐 Environment Setup

To test and run the eQual-based projects:

1. Clone eQual framework (version dev-2.0):

   ```
   git clone https://github.com/equalframework/equal.git
   cd equal
   git checkout dev-2.0
   ```

2. Set up your package:

   ```
   mv packages packages.core
   cp -r /path/to/your/repository ./packages
   cp -r packages.core/core packages/core
   ```

3. Initialize environment:

   ```
   php run.php --do=init_fs
   php run.php --do=test_fs-consistency
   php run.php --do=test_db-connectivity
   php run.php --do=init_db
   php run.php --do=init_package --package=core --ignore_platform
   ```

4. Run your package tests:

   ```
   php run.php --do=test_package-consistency --package=your_package
   ```

Ensure `equal` is the root directory, and your repository content is copied into the `packages/` folder.

