- Example paths:
  - `classes/booking/Booking.class.php`
  - `views/booking/Booking.form.default.json`
  - `views/booking/Booking.list.default.json`
  - `i18n/fr/booking/Booking.json`

- Minimal entity class skeleton:
```php
<?php
namespace sale\booking;

use equal\orm\Model;

class Booking extends Model {
    public static function getColumns() {
        return [
            'name' => ['type' => 'string', 'required' => true]
        ];
    }
}
```

- Minimal form view reference:
```json
{ "layout": { "groups": [] } }
```

- Minimal list view reference:
```json
{ "layout": { "items": [] } }
```

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

- Note: A new entity usually requires class, views, and i18n updates together.
