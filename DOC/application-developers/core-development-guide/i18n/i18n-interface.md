# Interface Translation

Interface translation involves localizing static elements such as field labels, descriptions, menu items, and view titles. This is handled via JSON files located within [Packages](../../how-tos-references/package.md).

---

## Directory Structure

Each package contains an optional `i18n` folder. This folder must contain subfolder for each supported language code. Inside these folders, translations are stored in `.json` files named after the class they translate.

**Generic Path**: `packages/{package_name}/i18n/{lang}/{class_name}.json`

**Example**: `packages/sale/i18n/fr/Customer.json`

---

## JSON File Structure

The translation file contains metadata about the class, field translations (`model`), and UI layout translations (`view`).

### Root Properties

*   `name`: The singular name of the entity (e.g., "Partner").
*   `plural`: The plural name of the entity (e.g., "Partners").
*   `description`: A general description of the entity.

### The `model` Section

This section maps field names to their translated attributes.

*   `label`: The user-facing name of the field (Must start with a capital letter).
*   `description`: A brief summary or tooltip for the field (Should start with a capital letter and end with punctuation).
*   `help`: Detailed explanation (optional).
*   `selection`: Used for fields with a fixed set of options (like a `status` field), mapping internal keys to displayed values.

### The `view` Section

This section translates specific [Views](../views-ui/frontend-logic.md) and their internal layout sections (tabs, groups). The keys correspond to the view types (e.g., `form.default`, `list.default`).

*   `name`: The title of the view.
*   `layout`: An object mapping section IDs (e.g., `section.identity_info`) to their translated `label`.

### The `error` Section

Used for translating form validation errors or specific controller exceptions.

### Error Message Translations

Error messages raised through exceptions must be translatable.

#### 1. Exception-based error messages

When throwing an exception, the message string is used as the translation key:

```php
throw new \Exception('cannot_remove_non_proforma', EQ_ERROR_NOT_ALLOWED);
```

- The string `'cannot_remove_non_proforma'` acts as the **error identifier**.
- This identifier must be defined in the translation file of the related class, under the `"error"` section.

#### 2. Field-related error messages

By default, error keys are organized by **field name**.

Each field contains a set of error messages related to validation rules (for example: constraints, uniqueness).

Example:

```json
"error": {
    "login": {
        "invalid_email": "Must be a valid email address."
    },
    "firstname": {
        "too_short": "Must contain at least 2 characters.",
        "invalid_chars": "Must not contain symbolic characters."
    }
}
```

- These messages are typically triggered by validation logic.
- Structure: `error → field → error_code → translated message`

#### 3. Global (non-field) error messages

Errors that are **not linked to a specific field** must be defined under the `"errors"` key.

These errors are always triggered via `throw new Exception(...)`, either:

- in the class itself, or
- in one of its associated controllers.

Example:

```json
"error": {
    "customer_id": {
        "missing_mandatory": "Required field."
    },
    "center_id": {
        "missing_mandatory": "Required field."
    },
    "status": {
        "non_editable": "The booking status does not allow editing.",
        "undefined_product_id": "Status cannot be changed if no product is defined."
    },
    "errors": {
        "overbooking_detected": "Overbooking detected."
    }
}
```

- Structure for global errors: `error → errors → error_code → translated message`

### Comprehensive Example

**File**: `packages/accounting/i18n/fr/VatRule.json`

```json
{
    "name": "Règle de Tva",
    "plural": "Règles de Tva",
    "description": "Les règles comptables permettent de préciser sur quel compte une opération doit être imputée.",
    "model": {
        "name":{
            "label":"Nom", 
            "description": "Nom de la règle comptable.", 
            "help": ""
        },
        "type":{
            "label":"Type", 
            "description": "Type d'opération.", 
            "selection": {
                "purchase": "achat", 
                "sale": "vente"
            }
        }
    },
    "view": {
        "form.default": {
            "name": "Règles de Tva",
            "description": "Vue par défaut",
            "layout": {
                "section.details": {
                    "label": "Détails"
                }
            }
        },
        "list.default": {
            "name": "Liste des Règles de Tva",
            "description": "Tableau principal",
            "layout": {}
        }
    },
    "error": {}
}
```

---

## Menu Translation

Menus differ from standard Views as they do not bind to a Model field structure. Therefore, their translation files omit the `model` section. The `view` section targets the menu items directly.

```json
{
  "description": "Menu de navigation principal",
  "view": {
    "item.bookings": {
      "label": "Réservations",
      "description": "",
      "layout": {}
    },
    "item.planning": {
      "label": "Calendrier",
      "description": "",
      "layout": {}
    }
  }
}
```

---

## Inheritance and Overloading

Classes in eQual can extend other classes (e.g., `lodging\Contact` extending `sale\Contact`). Translation resolution follows the class hierarchy.

1.  **Inheritance**: If a child class does not translate a field, it inherits the translation from its parent.
2.  **Overloading**: If a child class provides a translation for a field that also exists in the parent, the child's translation takes precedence (is "overloaded").

This allows you to change the label of a generic field (e.g., changing "Name" to "Venue Name") in a specific context without altering the original parent class.

---

## Documentation files (.md)

In addition to `.json` files, you can place Markdown (`.md`) files in the `i18n` folder. These files should match the view ID (e.g., `Booking.list.default.md`) and are used to display rich help content contextually within the application (accessible from the right-side navigation).

**Example**: `packages/booking/i18n/fr/Booking.list.default.md`

```markdown
# Aide pour la liste des réservations
Cette page affiche toutes les réservations. Vous pouvez filtrer, trier et exporter les données selon vos besoins.
```

---

## Requesting Translations via API

The frontend retrieves these translations using the `config_i18n` controller.

```bash
$ ./equal.run --get=config_i18n --entity=core\\User --lang=fr
```

---