- Minimal i18n structure:
```json
{
  "name": "Booking",
  "plural": "Bookings",
  "description": "Booking entity.",
  "model": {},
  "view": {}
}
```

- Root metadata:
```json
{ "name": "Booking", "plural": "Bookings", "description": "Booking entity." }
```

- Field translation:
```json
"status": {
  "label": "Status",
  "description": "Current booking state.",
  "help": "Use this field to track progress."
}
```

- Selection translation:
```json
"selection": { "draft": "Draft", "confirmed": "Confirmed" }
```

- View translation:
```json
"Booking.form.default": {
  "name": "Booking",
  "layout": { "section.general": { "label": "General" } }
}
```

- Layout section translation:
```json
"layout": { "section.general": { "label": "General information" } }
```

- Actions translation:
```json
"actions": { "action.confirm": { "label": "Confirm", "description": "Confirm this booking." } }
```

- Error translation:
```json
"error": { "status": { "non_editable": "Status cannot be edited." } }
```
