- Action path: `actions/booking/Booking/confirm.php`
- Data provider path: `data/booking/Booking/list.php`
- Action CLI name from path: `packages/sale/actions/booking/Booking/confirm.php` -> `sale_booking_Booking_confirm`
- Action CLI call: `./equal.run --do=sale_booking_Booking_confirm --announce=true`
- Internal action call from another controller:
```php
eQual::run('do', 'sale_booking_Booking_confirm', ['id' => $booking_id]);
```

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
