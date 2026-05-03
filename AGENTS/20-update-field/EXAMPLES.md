- Add scalar field:
```php
'code' => ['type' => 'string', 'required' => true]
```

- Add relation field:
```php
'customer_id' => ['type' => 'many2one', 'foreign_object' => 'sale\\Customer']
```

- Add field to form view:
```json
{ "type": "field", "value": "code" }
```

- Add field to list view:
```json
{ "type": "field", "value": "customer_id", "sortable": true }
```

- i18n field entry:
```json
"code": {
  "label": "Code",
  "description": "Unique booking code.",
  "help": "Enter a unique value."
}
```

- Warning: Adding a field to the class without updating views and i18n is incomplete.

- Rename checklist example: rename in class, update all view references, update i18n keys, remove old key usages.
