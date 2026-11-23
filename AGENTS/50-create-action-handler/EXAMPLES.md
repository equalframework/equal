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
  'policies' => ['confirmable'],
  'function' => 'doConfirm'
]
```

The `confirmable` policy must be declared in `getPolicies()` and must validate every predictable prerequisite, such as the current status and the presence and compatibility of required relations. It must complete those checks for the full target collection before the action starts mutating objects.

The action body then contains only the success path:

```php
protected static function doConfirm($self) {
    $self->update(['status' => 'confirmed']);
}
```

Do not use an empty policy list for a mutating inline action. Do not add functional rejection, `try/catch`, or SQL transaction management to the `do*` method.

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
