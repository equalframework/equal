# JSON Schemas

JSON schemas define data exchange formats that comply with structured rules and official standards. They serve a dual purpose: documenting data structure and enabling validation. The format used is JSON, following the [JSON Schema](https://json-schema.org/) validation specification, version **2020-12**.

## Organization

### File Structure

JSON schemas are stored in the `schemas/` folder within each functional package:

```
packages/
  <package>/
    schemas/
      <schema-name>.json
```

**Examples:**

- `packages/finance/schemas/bank-statement.json`
- `packages/identity/schemas/user.json`

General schema-related logic at the core level is placed in:

```
core/schemas/
```

### Schema Identifier (`$id`)

Each JSON schema includes a unique `$id` field that follows a URN-based format:

```
urn:equal:json-schema:<package>:<schema-name>
```

**Examples:**

```json
{
  "$id": "urn:equal:json-schema:finance:bank-statement"
}
```

```json
{
  "$id": "urn:equal:json-schema:identity:user"
}
```

This identifier is used internally to reference and resolve schemas, including as `$ref` targets.

### Resolution Rules

Schema resolution follows these rules:

- Only schemas with a valid URN using the `urn:equal:json-schema:` prefix are supported.
- The `<package>` segment must match the name of the functional module.
- The `<schema-name>` must correspond to a `.json` file under the module's `schemas/` folder.
- Resolution is local; no external HTTP fetch is attempted.

---

## Usage

### Retrieving a Schema

The schema resolution controller retrieves schemas by their `$id`:

```
core/data/json-schema.php
```

**Request example:**

```
?get=core_json-schema&id=urn:equal:json-schema:finance:bank-statement
```

This controller maps the URN to a file path based on the standard structure under `packages/<package>/schemas/`.

### Validating JSON Data

A dedicated controller validates JSON data against a registered schema:

```
core/data/json-validate.php
```

#### Request

Send a POST request with the following JSON body:

| Field  | Type            | Description                                                                    |
| ------ | --------------- | ------------------------------------------------------------------------------ |
| `id`   | string          | The `$id` of the schema (e.g., `urn:equal:json-schema:finance:bank-statement`) |
| `data` | object or array | The JSON payload to be validated                                               |

**Example request body:**

```json
{
  "id": "urn:equal:json-schema:finance:bank-statement",
  "data": {
    "account": "BE123456789",
    "date": "2025-05-15",
    "entries": []
  }
}
```

#### Response

The response includes a `valid` boolean and, if validation fails, an `errors` array detailing each issue.

**Successful validation:**

```json
{
  "valid": true,
  "errors": []
}
```

**Failed validation:**

```json
{
  "valid": false,
  "errors": [
    {
      "path": "/date",
      "message": "String is not a valid date"
    },
    {
      "path": "/entries",
      "message": "Array must contain at least 1 item"
    }
  ]
}
```

#### Validation Details

- The schema is resolved using the same logic described in the [Retrieving a Schema](#retrieving-a-schema) section.
- Validation follows the [JSON Schema 2020-12](https://json-schema.org/) specification.
- The `path` field uses [JSON Pointer](https://json-schema.org/understanding-json-schema/reference/annotations.html#json-pointer) notation.

---