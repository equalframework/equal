- Form view path: `views/booking/Booking.form.default.json`
- List view path: `views/booking/Booking.list.default.json`

- Form section with explicit `id`:
```json
{ "id": "section.general", "label": "General", "rows": [] }
```

- Form field item:
```json
{ "type": "field", "value": "name", "widget": { "type": "string" } }
```

- List field item:
```json
{ "type": "field", "value": "status", "sortable": true }
```

- i18n `view` entry:
```json
"Booking.form.default": {
  "name": "Booking",
  "layout": { "section.general": { "label": "General" } }
}
```

- Warning: Section IDs in views must be translated.
