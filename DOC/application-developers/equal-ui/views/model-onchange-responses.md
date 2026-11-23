# Handling `model_onchange` Responses

`model_onchange` allows the server to react immediately to a change made in a form or an editable list.

Its response can:

- update one or more values;
- change a relational domain;
- replace the available selection;
- change a field's visibility, required state, or readonly state;
- report validation errors without waiting for `model_update`.

Errors returned by `model_onchange` are stored by the front end and remain visible after widgets are rendered again.

---

## Request Sent to the Server

A request has the following shape:

```json
{
  "entity": "sale\\order",
  "view_id": "form.default",
  "changes": {
    "date_start": "2026-11-13"
  },
  "values": {
    "id": 42,
    "date_start": "2026-11-13",
    "date_end": "2026-11-10",
    "state": "draft"
  },
  "lang": "en"
}
```

| Property | Description |
| --- | --- |
| `entity` | Entity being edited. |
| `view_id` | Identifier of the active view. |
| `changes` | Values changed directly by the user. |
| `values` | Current object state, exported in the `onchange` context. |
| `lang` | Active language. |

For files and binary content, only metadata is sent in `changes`.

For other values, the front end currently does not send an onchange request when the serialized value exceeds 1,000 characters.

---

## Response Formats

### Direct Values

The server can return values directly:

```json
{
  "amount": 100,
  "active": true,
  "date_end": null
}
```

Each top-level property identifies a field.

Direct arrays are also supported for `many2many` fields:

```json
{
  "category_ids": [1, 2, -3]
}
```

A `many2one` value can use the conventional `{id, name}` shape:

```json
{
  "customer_id": {
    "id": 42,
    "name": "Test Customer"
  }
}
```

### Field Descriptors

To return several instructions for the same field, use a descriptor:

```json
{
  "amount": {
    "value": 100,
    "readonly": false,
    "required": true,
    "errors": {
      "amount_exceeds_limit": "maximum_allowed_is_80"
    }
  }
}
```

---

## Supported Descriptor Properties

| Property | Behavior |
| --- | --- |
| `value` | Updates the field value in the `Model`. |
| `domain` | Replaces the dynamic domain of a `many2one` or `many2many` field. |
| `selection` | Replaces and normalizes the available selection. |
| `visible` | Re-evaluates widget visibility. |
| `readonly` | Re-evaluates whether the widget is editable. |
| `required` | Changes whether the field is required. |
| `errors` | Replaces or removes the field's validation errors. |

Other descriptor properties are copied to the widget configuration. This makes it possible to update some widget-specific options dynamically, including:

- `description`;
- `min` and `max`;
- `align`;
- `header`;
- `has_action_create`;
- `has_action_open`;
- `has_action_select`.

This mechanism is generic, but not every configuration property can be changed dynamically. Changing `type` or `usage`, for example, does not replace the class of an already instantiated widget.

---

## Handling `errors`

### Recommended Format

The recommended format is a map from a stable error code to a message or translation key:

```json
{
  "date_start": {
    "errors": {
      "invalid_period": "start_date_must_precede_end_date"
    }
  }
}
```

Several errors can be returned for one field:

```json
{
  "amount": {
    "errors": {
      "amount_exceeds_limit": "maximum_allowed_is_80",
      "amount_inconsistent_with_total": "amount_must_not_exceed_total"
    }
  }
}
```

The front end stores every error, although the interface currently displays only the first error for a field.

Arrays and strings are also accepted:

```json
{
  "field": {
    "errors": ["First error", "Second error"]
  }
}
```

```json
{
  "field": {
    "errors": "Invalid value"
  }
}
```

Prefer the code-based map because it supports translation and provides a stable contract.

### Differential Semantics

The presence or absence of `errors` has a precise meaning.

#### Property Omitted

```json
{
  "amount": {
    "value": 100
  }
}
```

The existing error state of the field remains unchanged.

#### Non-empty Errors

```json
{
  "amount": {
    "errors": {
      "amount_exceeds_limit": "maximum_allowed_is_80"
    }
  }
}
```

The new errors replace all previous errors for the field.

#### Empty Errors

```json
{
  "amount": {
    "errors": {}
  }
}
```

All errors for the field are removed. The following values are also considered empty:

```json
{
  "errors": []
}
```

```json
{
  "errors": null
}
```

`false` and an empty string have the same effect, but `{}` is the recommended form.

---

## Returning a Value and Errors Together

A field can receive a computed value while remaining invalid:

```json
{
  "amount": {
    "value": 100,
    "errors": {
      "amount_exceeds_limit": "maximum_allowed_is_80"
    }
  }
}
```

For a `many2one` field:

```json
{
  "customer_id": {
    "value": {
      "id": 42,
      "name": "Test Customer"
    },
    "errors": {
      "customer_inactive": "selected_customer_is_inactive"
    }
  }
}
```

To update a value and remove its errors:

```json
{
  "amount": {
    "value": 75,
    "errors": {}
  }
}
```

---

## Cross-field Validation

An onchange response can add or remove errors on several fields even when the user changed only one field:

```json
{
  "date_start": {
    "errors": {
      "invalid_period": "start_date_must_precede_end_date"
    }
  },
  "date_end": {
    "errors": {
      "invalid_period": "end_date_must_follow_start_date"
    }
  }
}
```

After the user corrects the values:

```json
{
  "date_start": {
    "errors": {}
  },
  "date_end": {
    "errors": {}
  }
}
```

The server must explicitly return `errors: {}` for every field whose errors must be removed.

---

## Front-end Storage

Errors are not added directly to business objects. The `Model` stores them in a separate map organized by object and field:

```text
object_id
└── field
    └── errors
```

Conceptually, the map looks like this:

```json
{
  "42": {
    "date_start": {
      "invalid_period": "start_date_must_precede_end_date"
    },
    "amount": {
      "amount_exceeds_limit": "maximum_allowed_is_80"
    }
  }
}
```

Keeping errors separate prevents them from being included accidentally in a `model_update` payload.

The `Model` provides these main operations:

```ts
model.setFieldErrors(objectId, field, errors);
model.getFieldErrors(objectId, field);
model.getObjectFieldErrors(objectId);
model.clearFieldErrors(objectId, field);
```

Values are copied when they are stored and when they are read to prevent mutation by reference.

---

## Refreshing Visual Marks

After interpreting a `model_onchange` response, the layout calls:

```ts
refreshFieldMarks(objectId);
```

This method reconciles the DOM with the errors stored in the `Model`. For each widget:

- `markFieldAsInvalid()` is called when the field has errors;
- `clearFieldMark()` is called when the field no longer has errors.

Marking a field as invalid:

- adds `sb-widget--invalid`;
- adds the relevant Material classes to text fields and selections;
- adds `aria-invalid="true"` to controls;
- displays the message in the field's helper text;
- makes the validation message persistent.

When the error is removed:

- invalid state classes are removed;
- `aria-invalid` is removed;
- the error message is removed;
- the field's original description is restored.

The refresh happens after widget rendering so that visibility changes or widget reconstruction do not discard the marks.

This mechanism works:

- in forms;
- in editable lists;
- for several objects at the same time in a list.

---

## Translating Errors

For an error such as:

```json
{
  "amount": {
    "errors": {
      "amount_exceeds_limit": "maximum_allowed_is_80"
    }
  }
}
```

The front end first tries a field-specific translation based on:

- the field name;
- the message;
- the error code.

If no field-specific translation exists, it tries the corresponding generic translation key:

```text
SB_ERROR_AMOUNT_EXCEEDS_LIMIT
```

If neither translation exists, the message returned by the server is displayed.

---

## Errors Returned by `model_update`

Errors returned by `model_update` use the same persistent storage. The following error categories can mark fields:

- `INVALID_PARAM`, with a map keyed by field;
- `NOT_ALLOWED`, with a descriptor for each field;
- `CONFLICT_OBJECT`, with a map of conflicting fields.

Example:

```json
{
  "errors": {
    "INVALID_PARAM": {
      "amount": {
        "amount_exceeds_limit": "maximum_allowed_is_80"
      }
    }
  }
}
```

When an object is passed to `displayErrorFeedback()`:

1. errors are stored in the `Model`;
2. `refreshFieldMarks()` runs;
3. the affected fields are marked;
4. the corresponding snackbars are displayed when the `snack` option is enabled.

Errors that are not associated with a field, such as a general `MISSING_PARAM`, produce a snackbar but do not mark a widget.

As a result, `model_update` errors remain visible after a new render. A later onchange response can remove them by explicitly returning `errors: {}`.

---

## Error Lifecycle

Errors remain stored until one of the following events occurs:

- an onchange response returns an empty `errors` value for the field;
- `clearFieldErrors()` is called explicitly;
- the object is reset with `Model.reset()`;
- the object is replaced with `Model.set()`;
- the collection is reloaded successfully by `Model.refresh()`.

A value change made with `Model.change()` does not automatically clear errors.

---

## Compatibility with Legacy Responses

Existing direct-value responses remain supported:

```json
{
  "name": "New name",
  "amount": 100,
  "active": true,
  "date": null
}
```

Direct `many2one` values also remain supported:

```json
{
  "customer_id": {
    "id": 42,
    "name": "Test Customer"
  }
}
```

The front end distinguishes a `many2one` value from a descriptor as follows:

- an object with `{id, name}` is treated as a value;
- an object containing `value`, `domain`, `selection`, `visible`, `readonly`, `required`, or `errors` is treated as a descriptor;
- an object with neither `id` nor `name` is also treated as a descriptor.

Therefore:

```json
{
  "customer_id": {
    "id": 42,
    "name": "Test Customer"
  }
}
```

is a direct value, while:

```json
{
  "customer_id": {
    "value": {
      "id": 42,
      "name": "Test Customer"
    },
    "errors": {}
  }
}
```

is a descriptor.

---

## Back-end Recommendations

### Return a Normal Response for Onchange Validation

An inconsistency detected during editing should preferably be returned in a successful HTTP response:

```json
{
  "field": {
    "errors": {
      "error_code": "error_message"
    }
  }
}
```

HTTP exceptions produced by `model_onchange` are currently logged by the front end, but their content is not converted into field marks.

### Always Return the State of Validated Fields

If the server previously added an error to a field, it must explicitly return:

```json
{
  "field": {
    "errors": {}
  }
}
```

when the field becomes valid again. Omitting `errors` means "leave the current state unchanged," not "remove the errors."

### Use Stable Error Codes

Use stable error codes as keys:

```json
{
  "errors": {
    "invalid_period": "start_date_must_precede_end_date"
  }
}
```

This enables:

- front-end translation;
- a different message for each field;
- fallback to a global translation;
- changes to wording without changes to the functional contract.

### Keep Validation in `model_update`

Onchange validation gives users immediate feedback, but it does not replace validation in `model_update`.

The front end does not currently block saving solely because its error map contains errors. The server must remain the final authority and reject every invalid update.
