- Minimal form view reference:
```json
{ "layout": { "groups": [
    {
        "sections": [
            {
                "rows": [
                    {
                        "columns": [
                            {
                                "width": "100%",
                                "items": []
                            }
                        ]
                    }
                ]
            }
        ]
    }
] } }
```

- Minimal list view reference:
```json
{ "layout": { "items": [] } }
```

- Form section with explicit `id`:
```json
{ "id": "section.general", "label": "General", "rows": [] }
```

- Form field item:
```json
{ "type": "field", "value": "name", "width": "100%", "widget": { "type": "string" } }
```

- List field item:
```json
{ "type": "field", "value": "status", "width": "10%", "sortable": true }
```
