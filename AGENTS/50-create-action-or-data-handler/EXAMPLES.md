- Action path: `actions/booking/Booking/confirm.php`
- Data provider path: `data/booking/Booking/list.php`

- Inline action in `getActions()`:
```php
'confirm' => [
  'description' => 'Confirm booking.',
  'policies' => [],
  'function' => 'doConfirm'
]
```

- View action reference:
```json
"actions": { "action.confirm": { "type": "action" } }
```

- i18n action translation:
```json
"actions": { "action.confirm": { "label": "Confirm", "description": "Confirm this booking." } }
```

- Error translation:
```json
"error": { "status": { "already_confirmed": "Booking is already confirmed." } }
```

- Warning: Actions exposed in views must have translations.
