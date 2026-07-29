- Minimal form view file:
```json
{
    "name": "Default form",
    "layout": {
        "groups": [
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
        ]
    }
}
```

- Minimal list view file:
```json
{
    "name": "Default list",
    "layout": {
        "items": []
    }
}
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
